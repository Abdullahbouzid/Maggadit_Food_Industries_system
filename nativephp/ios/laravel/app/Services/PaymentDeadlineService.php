<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PaymentDeadlineSetting;
use App\Models\StockReservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PaymentDeadlineService
{
    /**
     * Apply payment deadline to a newly approved cash invoice.
     */
    public function applyDeadline(Invoice $invoice): ?Carbon
    {
        $setting = $this->getActiveSetting($invoice->payment_type);

        if (!$setting) {
            return null;
        }

        $deadline = now()->addHours($setting->hours_limit);

        $invoice->update(['payment_deadline_at' => $deadline]);

        return $deadline;
    }

    /**
     * Extend the payment deadline for an invoice.
     */
    public function extend(Invoice $invoice, int $additionalHours, User $user, ?string $notes = null): Carbon
    {
        return DB::transaction(function () use ($invoice, $additionalHours, $user, $notes) {
            $currentDeadline = $invoice->payment_deadline_at ?? now();
            $newDeadline = $currentDeadline->copy()->addHours($additionalHours);

            $invoice->update(['payment_deadline_at' => $newDeadline]);

            AuditLog::create([
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'event' => 'deadline_extended',
                'old_values' => ['payment_deadline_at' => $invoice->getOriginal('payment_deadline_at')],
                'new_values' => ['payment_deadline_at' => $newDeadline, 'notes' => $notes],
                'user_id' => $user->id,
                'user_type' => 'user',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $newDeadline;
        });
    }

    /**
     * Expire all unpaid invoices whose deadline has passed.
     * Called hourly from the scheduled command.
     */
    public function expireUnpaid(): array
    {
        $expired = [];

        $invoices = Invoice::where('status', InvoiceStatus::Approved->value)
            ->where('payment_type', 'cash')
            ->where('payment_deadline_at', '<', now())
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->lockForUpdate()
            ->get();

        foreach ($invoices as $invoice) {
            try {
                DB::transaction(function () use ($invoice, &$expired) {
                    $invoice->update(['status' => InvoiceStatus::Expired->value]);

                    $this->releaseAssociatedReservations($invoice);

                    app(InventoryService::class)->reverseCommit($invoice);

                    $this->notifyStakeholders($invoice);

                    $expired[] = $invoice;
                });
            } catch (\Exception $e) {
                \Log::error(sprintf(
                    'Failed to expire invoice #%d: %s',
                    $invoice->id,
                    $e->getMessage(),
                ));
            }
        }

        return $expired;
    }

    /**
     * Release all stock reservations associated with an invoice.
     */
    private function releaseAssociatedReservations(Invoice $invoice): void
    {
        $reservations = StockReservation::where('reserved_for_type', Invoice::class)
            ->where('reserved_for_id', $invoice->id)
            ->where('status', 'active')
            ->get();

        foreach ($reservations as $reservation) {
            app(InventoryService::class)->release($reservation->id);
        }
    }

    /**
     * Get the active payment deadline setting for a payment type.
     */
    protected function getActiveSetting(string $paymentType): ?PaymentDeadlineSetting
    {
        return PaymentDeadlineSetting::where('payment_type', $paymentType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Notify customer and admin about the expired invoice.
     */
    private function notifyStakeholders(Invoice $invoice): void
    {
        $customer = $invoice->customer;

        if ($customer && $customer->fcm_token) {
            $notification = new \App\Notifications\InvoiceExpired($invoice);
            Notification::send([$customer], $notification);
        }
    }
}
