<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OrderLifecycleServiceInterface;
use App\Contracts\QuotationServiceInterface;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Exceptions\InvalidTransitionException;
use App\Helpers\UnitOfWork;
use App\Contracts\InventoryServiceInterface;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

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
            $current = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);

            if (! $current) {
                throw new InvalidTransitionException(
                    'Order', $order->status, $newStatus->value, $order->id,
                );
            }

            if (!$this->canTransitionTo($order, $newStatus)) {
                throw new InvalidTransitionException(
                    'Order', $order->status, $newStatus->value, $order->id,
                );
            }

            $order->update(['status' => $newStatus->value]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromSystem ? null : $current->value,
                'to_status' => $newStatus->value,
                'changed_by' => $user?->id,
                'notes' => $notes,
            ]);

            $this->performSideEffects($order, $current, $newStatus, $user, $notes);

            return $order;
        });
    }

    public function canTransitionTo(Order $order, OrderStatus $newStatus): bool
    {
        $current = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);

        if (! $current) {
            return false;
        }

        if ($current === $newStatus) {
            return false;
        }

        return in_array($newStatus->value, self::$transitions[$current->value] ?? [], true);
    }

    public function getAllowedTransitions(Order $order): array
    {
        $current = $order->status instanceof OrderStatus ? $order->status : OrderStatus::tryFrom($order->status);

        if (! $current) {
            return [];
        }

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

            // Auto-create quotation so the customer app can display it
            $quotation = $order->quotation()->first();
            if (! $quotation) {
                $quotation = app(QuotationServiceInterface::class)->createFromOrder($order, $user);
            }

            // Send in-app notification + push to the customer
            $customer = $order->customer;
            if ($customer) {
                $total = (float) ($quotation->total ?? 0);
                $notification = Notification::create([
                    'subject_en' => 'New price quote available',
                    'subject_ar' => 'عرض سعر جديد متاح لطلبك',
                    'body_en' => 'A new price quote for order #' . $order->order_number . ' is ready. Total: ' . number_format($total, 2),
                    'body_ar' => 'عرض سعر جديد لطلبية رقم ' . $order->order_number . ' بإجمالي ' . number_format($total, 2) . ' ر.س. يappa في تطبيق العميل.',
                    'image' => null,
                    'video' => null,
                    'group' => 'quotation',
                    'channel' => 'push',
                    'icon_type' => 'quote',
                    'customers' => [$customer->id],
                    'is_sent' => true,
                    'scheduled_at' => now(),
                ]);

                $this->sendFcmPush($notification);
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

    /**
     * Send an FCM push notification to the customer.
     */
    private function sendFcmPush(\App\Models\Notification $notification): void
    {
        try {
            if (! class_exists(Factory::class)) {
                \Illuminate\Support\Facades\Log::error('FCM: kreait/firebase-php not installed');

                return;
            }

            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/google-services.json'));

            $messaging = $factory->createMessaging();

            $message = CloudMessage::fromArray([
                'notification' => [
                    'title' => $notification->subject_ar,
                    'body' => $notification->body_ar,
                    'image' => $notification->image ? asset('uploads/'.$notification->image) : null,
                ],
                'data' => [
                    'id' => (string) $notification->id,
                    'icon_type' => $notification->icon_type ?? 'notification',
                    'type' => 'quotation',
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'maggadit_channel',
                        'priority' => 'high',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ]);

            $customerIds = is_array($notification->customers) ? $notification->customers : json_decode($notification->customers, true);
            if (is_array($customerIds) && count($customerIds) > 0) {
                $tokens = \App\Models\Customer::whereIn('id', $customerIds)
                    ->whereNotNull('fcm_token')
                    ->pluck('fcm_token')
                    ->toArray();

                if (count($tokens) > 0) {
                    $messaging->sendMulticast($message, $tokens);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('FCM Push Error: '.$e->getMessage());
        }
    }
}
