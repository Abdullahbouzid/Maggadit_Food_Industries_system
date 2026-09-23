<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Events\PaymentRecorded;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AuditLog;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService implements PaymentServiceInterface
{
    public function record(
        Invoice $invoice,
        float $amount,
        string $method,
        ?string $reference = null,
        ?int $receivedBy = null,
        ?string $notes = null,
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $receivedBy, $notes) {
            $paymentNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad((string) Str::random(8), 8, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'currency_id' => $invoice->currency_id ?? 1,
                'exchange_rate' => $invoice->exchange_rate ?? 1.0,
                'method' => $method,
                'reference' => $reference,
                'received_by' => $receivedBy,
                'paid_at' => now(),
                'notes' => $notes,
            ]);

            $newPaidAmount = (float) $invoice->paid_amount + $amount;
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'paid_at' => $invoice->paid_at ?? now(),
                'status' => $newPaidAmount >= (float) $invoice->total_amount
                    ? InvoiceStatus::Paid->value
                    : InvoiceStatus::PartiallyPaid->value,
            ]);

            if ($method === 'wallet') {
                $walletService = app(WalletService::class);
                try {
                    $walletService->debit(
                        $invoice->customer,
                        $amount,
                        $invoice,
                        'Payment via wallet',
                        $receivedBy,
                    );
                } catch (InsufficientBalanceException $e) {
                    throw $e;
                }
            }

            AuditLog::create([
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'event' => 'payment_recorded',
                'old_values' => ['paid_amount' => $invoice->getOriginal('paid_amount')],
                'new_values' => ['paid_amount' => $newPaidAmount, 'status' => $invoice->status],
                'user_id' => $receivedBy,
                'user_type' => 'user',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            event(new PaymentRecorded($payment));

            return $payment;
        });
    }

    public function void(CreditNote $creditNote, ?string $reason = null): void
    {
        DB::transaction(function () use ($creditNote, $reason) {
            $invoice = $creditNote->applied_to_invoice_id
                ? Invoice::find($creditNote->applied_to_invoice_id)
                : null;

            if ($invoice) {
                $reduction = min((float) $invoice->paid_amount, (float) $creditNote->total);
                $invoice->decrement('paid_amount', $reduction);

                AuditLog::create([
                    'auditable_type' => Invoice::class,
                    'auditable_id' => $invoice->id,
                    'event' => 'credit_note_voided',
                    'old_values' => ['paid_amount' => $invoice->getOriginal('paid_amount')],
                    'new_values' => ['paid_amount' => $invoice->paid_amount, 'reason' => $reason],
                    'user_id' => request()->user()?->id,
                    'user_type' => 'user',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $walletService = app(WalletService::class);
            $walletService->adjust(
                $creditNote->customer,
                (float) $creditNote->total * -1,
                'Credit note voided: '.$creditNote->credit_note_number,
                (int) (request()->user()?->id ?? 0),
            );
        });
    }
}
