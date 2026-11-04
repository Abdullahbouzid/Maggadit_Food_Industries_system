<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Quotation;
use App\Models\Invoice;

/**
 * Builds a customer-facing, Arabic, step-by-step view of an order's lifecycle.
 *
 * Used by:
 *  - Customer app  : order_details + order_history (مسار B)
 *  - Admin dashboard: order show page (مسار A)
 *
 * The step list is a linear projection of the state machine defined in
 * OrderLifecycleService::$transitions. Terminal negative states (cancelled /
 * rejected / returned) collapse the remaining steps into a single final step.
 */
class OrderStepPresenter
{
    /**
     * Steps in order, mapped to the statuses that complete them.
     */
    private const STEPS = [
        [
            'key' => 'submitted',
            'label' => 'تم إرسال الطلب',
            'icon' => 'fa-send',
            'color' => 'purple',
            'statuses' => [OrderStatus::New->value],
        ],
        [
            'key' => 'review',
            'label' => 'قيد المراجعة',
            'icon' => 'fa-magnifying-glass',
            'color' => 'blue',
            'statuses' => [OrderStatus::PendingReview->value],
        ],
        [
            'key' => 'quotation',
            'label' => 'عرض السعر',
            'icon' => 'fa-file-invoice-dollar',
            'color' => 'indigo',
            'statuses' => [OrderStatus::AdminEdited->value, OrderStatus::QuotationSent->value],
        ],
        [
            'key' => 'customer_response',
            'label' => 'رد العميل',
            'icon' => 'fa-user-check',
            'color' => 'teal',
            'statuses' => [
                OrderStatus::CustomerApproved->value,
                OrderStatus::CustomerRejected->value,
                OrderStatus::CustomerRequestedChanges->value,
            ],
        ],
        [
            'key' => 'invoice',
            'label' => 'إصدار الفاتورة',
            'icon' => 'fa-receipt',
            'color' => 'amber',
            'statuses' => [OrderStatus::InvoiceGenerated->value, OrderStatus::PendingApproval->value],
        ],
        [
            'key' => 'delivery',
            'label' => 'التوصيل',
            'icon' => 'fa-truck-fast',
            'color' => 'sky',
            'statuses' => [OrderStatus::Approved->value, OrderStatus::InDelivery->value],
        ],
        [
            'key' => 'completed',
            'label' => 'تم التوصيل',
            'icon' => 'fa-circle-check',
            'color' => 'green',
            'statuses' => [OrderStatus::Delivered->value],
        ],
    ];

    /**
     * Negative terminal states and the message shown instead of the remaining steps.
     */
    private const NEGATIVE_STATES = [
        OrderStatus::Cancelled->value => ['label' => 'تم إلغاء الطلبية', 'icon' => 'fa-circle-xmark', 'color' => 'red'],
        OrderStatus::Rejected->value => ['label' => 'تم رفض الفاتورة', 'icon' => 'fa-circle-xmark', 'color' => 'red'],
        OrderStatus::Returned->value => ['label' => 'تم إرجاع الطلبية', 'icon' => 'fa-rotate-left', 'color' => 'red'],
        OrderStatus::Expired->value => ['label' => 'انتهت صلاحية الفاتورة', 'icon' => 'fa-clock', 'color' => 'red'],
    ];

    /**
     * Return the step list for the given order.
     *
     * @return array<int, array{key:string,label:string,icon:string,color:string,state:string,date:?string,notes:?string}>
     */
    public function steps(Order $order): array
    {
        $currentValue = $this->currentStatusValue($order);
        $history = $this->historyMap($order);

        // Index of the furthest step the order has reached.
        $currentIndex = null;
        foreach (self::STEPS as $index => $step) {
            if (in_array($currentValue, $step['statuses'], true)) {
                $currentIndex = $index;
                break;
            }
        }

        $isNegative = isset(self::NEGATIVE_STATES[$currentValue]);

        $steps = [];
        foreach (self::STEPS as $index => $step) {
            $entry = $this->historyFor($step, $history);

            if ($currentIndex === null || $index > $currentIndex) {
                $state = 'pending';
            } elseif ($index < $currentIndex) {
                $state = 'done';
            } else {
                $state = 'current';
            }

            $steps[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'icon' => $step['icon'],
                'color' => $step['color'],
                'state' => $state,
                'date' => $entry['date'] ?? null,
                'notes' => $entry['notes'] ?? null,
            ];
        }

        // A rejection at the customer-decision step marks it as rejected.
        if ($currentValue === OrderStatus::CustomerRejected->value) {
            foreach ($steps as $i => $step) {
                if ($step['key'] === 'customer_response') {
                    $steps[$i]['state'] = 'rejected';
                    $steps[$i]['label'] = 'رفض العميل العرض';
                }
            }
        }

        if ($currentValue === OrderStatus::CustomerRequestedChanges->value) {
            foreach ($steps as $i => $step) {
                if ($step['key'] === 'quotation') {
                    $steps[$i]['state'] = 'current';
                    $steps[$i]['label'] = 'عرض السعر (طلب العميل تعديلات)';
                }
            }
        }

        // Canceled before the quotation step was ever reached.
        if ($isNegative) {
            $meta = self::NEGATIVE_STATES[$currentValue];
            $steps[] = [
                'key' => 'terminal_'.$currentValue,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'state' => 'rejected',
                'date' => $history['__last']['date'] ?? null,
                'notes' => $history['__last']['notes'] ?? null,
            ];
        }

        return $steps;
    }

    /**
     * The current status as a plain string.
     */
    public function currentStatusValue(Order $order): string
    {
        $status = $order->status;

        if ($status instanceof OrderStatus) {
            return $status->value;
        }

        return (string) $status;
    }

    /**
     * Arabic label + a modifier class for the current status.
     *
     * @return array{label:string, class:string, badge:string}
     */
    public function currentStatusMeta(Order $order): array
    {
        $value = $this->currentStatusValue($order);
        $enum = OrderStatus::tryFrom($value);

        $label = $enum?->label() ?? $value;

        $badge = match ($value) {
            OrderStatus::New->value, OrderStatus::PendingReview->value, OrderStatus::QuotationSent->value,
            OrderStatus::PendingApproval->value => 'nova-badge-warning',
            OrderStatus::AdminEdited->value, OrderStatus::InvoiceGenerated->value, OrderStatus::InDelivery->value => 'nova-badge-info',
            OrderStatus::CustomerApproved->value, OrderStatus::Approved->value, OrderStatus::Delivered->value => 'nova-badge-success',
            OrderStatus::CustomerRejected->value, OrderStatus::Cancelled->value,
            OrderStatus::Rejected->value, OrderStatus::Expired->value, OrderStatus::Returned->value => 'nova-badge-danger',
            default => 'nova-badge-secondary',
        };

        $class = match (true) {
            $enum === null => 'text-gray-600 bg-gray-100',
            str_contains($badge, 'success') => 'text-emerald-700 bg-emerald-50',
            str_contains($badge, 'danger') => 'text-red-700 bg-red-50',
            str_contains($badge, 'warning') => 'text-amber-700 bg-amber-50',
            str_contains($badge, 'info') => 'text-blue-700 bg-blue-50',
            default => 'text-gray-700 bg-gray-100',
        };

        return ['label' => $label, 'class' => $class, 'badge' => $badge];
    }

    /**
     * True when the customer may act on the order (respond to a quotation).
     */
    public function canCustomerRespond(Order $order): bool
    {
        return $this->currentStatusValue($order) === OrderStatus::QuotationSent->value;
    }

    /**
     * The latest quotation awaiting a customer decision, if any.
     */
    public function pendingQuotation(Order $order): ?Quotation
    {
        return $order->quotation()
            ->where('customer_response', 'pending')
            ->latest('version')
            ->first();
    }

    /**
     * The invoice linked to the order, if any.
     */
    public function invoice(Order $order): ?Invoice
    {
        return $order->invoices()->latest('id')->first();
    }

    /**
     * Progress percentage (0-100) for a progress bar.
     */
    public function progress(Order $order): int
    {
        $value = $this->currentStatusValue($order);

        $percent = match ($value) {
            OrderStatus::New->value => 10,
            OrderStatus::PendingReview->value => 22,
            OrderStatus::AdminEdited->value => 34,
            OrderStatus::QuotationSent->value => 46,
            OrderStatus::CustomerApproved->value,
            OrderStatus::InvoiceGenerated->value => 58,
            OrderStatus::CustomerRequestedChanges->value => 34,
            OrderStatus::CustomerRejected->value => 46,
            OrderStatus::PendingApproval->value => 70,
            OrderStatus::Approved->value => 82,
            OrderStatus::InDelivery->value => 92,
            OrderStatus::Delivered->value => 100,
            default => 100,
        };

        return $percent;
    }

    /**
     * Format the status history for display.
     */
    public function timeline(Order $order): array
    {
        return $order->statusHistory()->with('changedBy')->get()->map(function (OrderStatusHistory $entry) {
            $toEnum = OrderStatus::tryFrom((string) $entry->to_status);
            $fromEnum = $entry->from_status ? OrderStatus::tryFrom((string) $entry->from_status) : null;

            return [
                'id' => $entry->id,
                'from' => $fromEnum?->label(),
                'to' => $toEnum?->label() ?? $entry->to_status,
                'by' => $entry->changedBy?->name,
                'notes' => $entry->notes,
                'date' => $entry->created_at?->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    /**
     * @return array<string, array{date: string, notes: ?string}>
     */
    private function historyMap(Order $order): array
    {
        $map = [];

        foreach ($order->statusHistory()->orderBy('id')->get() as $entry) {
            $to = (string) $entry->to_status;
            $map[$to] = [
                'date' => $entry->created_at?->format('Y-m-d H:i'),
                'notes' => $entry->notes,
            ];
        }

        $last = $order->statusHistory()->orderByDesc('id')->first();
        $map['__last'] = [
            'date' => $last?->created_at?->format('Y-m-d H:i'),
            'notes' => $last?->notes,
        ];

        return $map;
    }

    /**
     * @param  array<string, array{date: string, notes: ?string}>  $history
     * @return array{date: ?string, notes: ?string}
     */
    private function historyFor(array $step, array $history): array
    {
        foreach ($step['statuses'] as $status) {
            if (isset($history[$status])) {
                return $history[$status];
            }
        }

        return ['date' => null, 'notes' => null];
    }
}
