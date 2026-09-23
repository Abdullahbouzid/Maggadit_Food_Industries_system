<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ApprovalResult;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\OrderStatus;
use App\Events\InvoiceApproved;
use App\Events\InvoiceExpired;
use App\Exceptions\InvalidTransitionException;
use App\Helpers\UnitOfWork;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentDeadlineSetting;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\DeliveryService;
use App\Services\InventoryService;
use App\Services\OrderLifecycleService;
use App\Services\PaymentDeadlineService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceLifecycleService
{
    private static array $transitions = [
        InvoiceStatus::PendingApproval->value => [
            InvoiceStatus::Approved->value,
            InvoiceStatus::Rejected->value,
            InvoiceStatus::Cancelled->value,
        ],
        InvoiceStatus::Approved->value => [
            InvoiceStatus::Paid->value,
            InvoiceStatus::PartiallyPaid->value,
            InvoiceStatus::Expired->value,
            InvoiceStatus::Cancelled->value,
        ],
        InvoiceStatus::Rejected->value => [InvoiceStatus::Cancelled->value],
        InvoiceStatus::PartiallyPaid->value => [
            InvoiceStatus::Paid->value,
            InvoiceStatus::Expired->value,
            InvoiceStatus::Cancelled->value,
        ],
        InvoiceStatus::Paid->value => [],
        InvoiceStatus::Expired->value => [],
        InvoiceStatus::Cancelled->value => [],
    ];

    /**
     * Approve an invoice with all side effects:
     * a. Validate status transition
     * b. Commit inventory (FEFO) via InventoryService
     * c. Create Delivery (deferred)
     * d. Update Order.status → approved
     * e. Apply payment deadline or set due_date
     * f. Fire InvoiceApproved event (after transaction commit)
     */
    public function approve(
        Invoice $invoice,
        ?User $user = null,
        ?string $notes = null,
    ): ApprovalResult {
        $inventoryService = app(InventoryService::class);
        $orderLifecycle = app(OrderLifecycleService::class);
        $deliveryService = app(DeliveryService::class);
        $deadlineService = app(PaymentDeadlineService::class);

        $result = UnitOfWork::transactional(function ($registerEvent) use (
            $invoice, $user, $notes, $inventoryService, $orderLifecycle, $deliveryService, $deadlineService
        ) {
            if (!$this->canTransitionTo($invoice, InvoiceStatus::Approved)) {
                throw new InvalidTransitionException(
                    entityType: 'Invoice',
                    fromStatus: $invoice->status,
                    toStatus: InvoiceStatus::Approved->value,
                    entityId: $invoice->id,
                );
            }

            $invoice->update([
                'status' => InvoiceStatus::Approved->value,
                'approved_by' => $user?->id,
                'approved_at' => now(),
            ]);

            $committedBatches = $inventoryService->commit($invoice);

            $order = $invoice->order;
            if ($order && $orderLifecycle->canTransitionTo($order, OrderStatus::Approved)) {
                $orderLifecycle->transition($order, OrderStatus::Approved, $user, $notes, true);
            }

            if ($invoice->payment_type === PaymentType::Cash->value) {
                $deadlineService->applyDeadline($invoice);
            } else {
                $dueDate = now()->addDays((int) ($invoice->customer->payment_terms_days ?? 0));
                $invoice->update(['due_date' => $dueDate]);
            }

            $delivery = $deliveryService->assignDriver($invoice);

            if ($order && $orderLifecycle->canTransitionTo($order, OrderStatus::InDelivery)) {
                $orderLifecycle->transition($order, OrderStatus::InDelivery, $user, 'Delivery assigned', true);
            }

            if ($notes) {
                AuditLog::create([
                    'auditable_type' => Invoice::class,
                    'auditable_id' => $invoice->id,
                    'event' => 'approved',
                    'old_values' => $invoice->getOriginal(),
                    'new_values' => $invoice->getChanges(),
                    'user_id' => $user?->id,
                    'user_type' => 'user',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $registerEvent(new InvoiceApproved($invoice, $user));

            return new ApprovalResult(
                invoice: $invoice,
                committedBatches: $committedBatches,
                delivery: $delivery,
                deadline: $invoice->payment_deadline_at,
            );
        });

        return $result;
    }

    public function extendDeadline(Invoice $invoice, int $additionalHours, User $user, ?string $notes = null): Carbon
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

    public function markAsPaid(Invoice $invoice, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $user) {
            if (!$this->canTransitionTo($invoice, InvoiceStatus::Paid)) {
                throw new InvalidTransitionException(
                    'Invoice', $invoice->status, InvoiceStatus::Paid->value, $invoice->id
                );
            }

            $invoice->update([
                'paid_amount' => $invoice->total_amount,
                'paid_at' => now(),
                'status' => InvoiceStatus::Paid->value,
            ]);

            return $invoice;
        });
    }

    public function markAsPartiallyPaid(Invoice $invoice, float $amount, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount, $user) {
            if (!$this->canTransitionTo($invoice, InvoiceStatus::PartiallyPaid)) {
                throw new InvalidTransitionException(
                    'Invoice', $invoice->status, InvoiceStatus::PartiallyPaid->value, $invoice->id
                );
            }

            $newPaidAmount = (float) $invoice->paid_amount + $amount;
            $newStatus = $newPaidAmount >= (float) $invoice->total_amount
                ? InvoiceStatus::Paid->value
                : InvoiceStatus::PartiallyPaid->value;

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'paid_at' => $invoice->paid_at ?? now(),
                'status' => $newStatus,
            ]);

            return $invoice;
        });
    }

    public function reject(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $user, $reason) {
            if (!$this->canTransitionTo($invoice, InvoiceStatus::Rejected)) {
                throw new InvalidTransitionException(
                    'Invoice', $invoice->status, InvoiceStatus::Rejected->value, $invoice->id
                );
            }

            $invoice->update(['status' => InvoiceStatus::Rejected->value]);

            AuditLog::create([
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'event' => 'rejected',
                'old_values' => $invoice->getOriginal(),
                'new_values' => $invoice->getChanges(),
                'user_id' => $user?->id,
                'user_type' => 'user',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $invoice;
        });
    }

    public function cancel(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $user, $reason) {
            if (!$this->canTransitionTo($invoice, InvoiceStatus::Cancelled)) {
                throw new InvalidTransitionException(
                    'Invoice', $invoice->status, InvoiceStatus::Cancelled->value, $invoice->id
                );
            }

            $invoice->update(['status' => InvoiceStatus::Cancelled->value]);

            if ($invoice->status === InvoiceStatus::Approved->value) {
                app(InventoryService::class)->reverseCommit($invoice);
            }

            return $invoice;
        });
    }

    public function expire(Invoice $invoice, bool $fromSystem = true): Invoice
    {
        return DB::transaction(function () use ($invoice, $fromSystem) {
            $oldStatus = $invoice->getOriginal('status');
            $invoice->update(['status' => InvoiceStatus::Expired->value]);

            app(InventoryService::class)->reverseCommit($invoice);

            AuditLog::create([
                'auditable_type' => Invoice::class,
                'auditable_id' => $invoice->id,
                'event' => 'expired',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => InvoiceStatus::Expired->value],
                'user_id' => $fromSystem ? null : request()->user()?->id,
                'user_type' => 'system',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $invoice;
        });
    }

    public function canTransitionTo(Invoice $invoice, InvoiceStatus $status): bool
    {
        $current = InvoiceStatus::from($invoice->status);

        if ($current === $status) {
            return false;
        }

        return in_array($status->value, self::$transitions[$current->value] ?? [], true);
    }

    public function getAllowedTransitions(Invoice $invoice): array
    {
        $current = InvoiceStatus::from($invoice->status);

        return array_map(
            fn (string $status) => InvoiceStatus::from($status),
            self::$transitions[$current->value] ?? [],
        );
    }
}
