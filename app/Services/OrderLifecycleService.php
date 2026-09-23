<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderLifecycleServiceInterface;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Exceptions\InvalidTransitionException;
use App\Helpers\UnitOfWork;
use App\Contracts\InventoryServiceInterface;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderLifecycleService implements OrderLifecycleServiceInterface
{
    /**
     * Allowed transitions for each OrderStatus.
     *
     * Transition Side Effects:
     | From → To | Side Effects |
     |-----------|--------------|
     | new → pending_review | AuditLog only |
     | pending_review → admin_edited | AuditLog only |
     | admin_edited → quotation_sent | InventoryService::reserve لكل سطر + إرسال إشعار |
     | quotation_sent → customer_approved | إنشاء Invoice draft (pending_approval) |
     | quotation_sent → customer_rejected | → cancelled (auto) |
     | quotation_sent → customer_requested_changes | → admin_edited |
     | customer_rejected → cancelled | InventoryService::release |
     | customer_requested_changes → admin_edited | InventoryService::release |
     | invoice_generated → pending_approval | AuditLog only |
     | pending_approval → approved | InvoiceLifecycleService::approve (commit stock, create delivery) |
     | approved → in_delivery | DeliveryService::assignDriver |
     | in_delivery → delivered | Order.status=delivered + إشعار |
     | Any → cancelled | InventoryService::release (if was reserved) |
     | Any → returned | Return workflow |
     | Any → expired | InventoryService::release |
     */
    private static array $transitions = [
        OrderStatus::New->value => [OrderStatus::PendingReview->value, OrderStatus::Cancelled->value],
        OrderStatus::PendingReview->value => [OrderStatus::AdminEdited->value, OrderStatus::Cancelled->value],
        OrderStatus::AdminEdited->value => [OrderStatus::QuotationSent->value, OrderStatus::Cancelled->value],
        OrderStatus::QuotationSent->value => [
            OrderStatus::CustomerApproved->value,
            OrderStatus::CustomerRejected->value,
            OrderStatus::CustomerRequestedChanges->value,
        ],
        OrderStatus::CustomerApproved->value => [OrderStatus::InvoiceGenerated->value],
        OrderStatus::CustomerRejected->value => [OrderStatus::Cancelled->value],
        OrderStatus::CustomerRequestedChanges->value => [OrderStatus::AdminEdited->value],
        OrderStatus::InvoiceGenerated->value => [OrderStatus::PendingApproval->value, OrderStatus::Cancelled->value],
        OrderStatus::PendingApproval->value => [OrderStatus::Approved->value, OrderStatus::Rejected->value],
        OrderStatus::Approved->value => [OrderStatus::InDelivery->value, OrderStatus::Cancelled->value],
        OrderStatus::InDelivery->value => [
            OrderStatus::Delivered->value,
            OrderStatus::Returned->value,
            OrderStatus::Cancelled->value,
        ],
        OrderStatus::Delivered->value => [],
        OrderStatus::Returned->value => [],
        OrderStatus::Cancelled->value => [],
        OrderStatus::Rejected->value => [],
        OrderStatus::Expired->value => [],
    ];

    public function transition(
        Order $order,
        OrderStatus $newStatus,
        ?User $user = null,
        ?string $notes = null,
        bool $fromSystem = false,
    ): Order {
        return DB::transaction(function () use ($order, $newStatus, $user, $notes, $fromSystem) {
            $currentStatus = OrderStatus::from($order->status);

            if (!$this->canTransitionTo($order, $newStatus)) {
                throw new InvalidTransitionException(
                    'Order', $order->status, $newStatus->value, $order->id,
                );
            }

            $order->update(['status' => $newStatus->value]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromSystem ? null : $currentStatus->value,
                'to_status' => $newStatus->value,
                'changed_by' => $user?->id,
                'notes' => $notes,
            ]);

            $this->performSideEffects($order, $currentStatus, $newStatus, $user, $notes);

            return $order;
        });
    }

    public function canTransitionTo(Order $order, OrderStatus $newStatus): bool
    {
        $current = OrderStatus::from($order->status);

        if ($current === $newStatus) {
            return false;
        }

        return in_array($newStatus->value, self::$transitions[$current->value] ?? [], true);
    }

    public function getAllowedTransitions(Order $order): array
    {
        $current = OrderStatus::from($order->status);

        return array_map(
            fn (string $status) => OrderStatus::from($status),
            self::$transitions[$current->value] ?? [],
        );
    }

    public function markAsInvoiceGenerated(Order $order, ?User $user = null): Order
    {
        if ($this->canTransitionTo($order, OrderStatus::InvoiceGenerated)) {
            return $this->transition($order, OrderStatus::InvoiceGenerated, $user, 'Invoice generated');
        }

        return $order;
    }

    public function markAsInDelivery(Order $order, ?User $user = null): Order
    {
        if ($this->canTransitionTo($order, OrderStatus::InDelivery)) {
            return $this->transition($order, OrderStatus::InDelivery, $user, 'Delivery started');
        }

        return $order;
    }

    /**
     * Execute side effects for specific transitions.
     */
    private function performSideEffects(
        Order $order,
        OrderStatus $from,
        OrderStatus $to,
        ?User $user,
        ?string $notes,
    ): void {
        $inventoryService = app(InventoryServiceInterface::class);

        if ($to === OrderStatus::QuotationSent) {
            foreach ($order->items as $item) {
                $inventoryService->reserve(
                    itemId: $item->item_id,
                    itemPackId: $item->item_pack_id,
                    warehouseId: $order->warehouse_id,
                    quantity: (float) $item->quantity,
                    referenceType: Order::class,
                    referenceId: $order->id,
                );
            }
        }

        if (in_array($to, [OrderStatus::CustomerRejected, OrderStatus::CustomerRequestedChanges], true)
            && in_array($from, [OrderStatus::QuotationSent, OrderStatus::Approved, OrderStatus::InDelivery], true)) {
            $reservations = \App\Models\StockReservation::where('reserved_for_type', Order::class)
                ->where('reserved_for_id', $order->id)
                ->where('status', 'active')
                ->get();

            foreach ($reservations as $reservation) {
                $inventoryService->release($reservation->id);
            }
        }
    }
}
