<?php
declare(strict_types=1);

namespace App\Enums;

enum DeliveryStage: string
{
    case Shipping = 'shipping';
    case OutForDelivery = 'out_for_delivery';
    case Unloading = 'unloading';
    case Received = 'received';
    // Written by DeliveryService::cancel(), so it must exist in the enum too.
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Shipping => 'جاري الشحن',
            self::OutForDelivery => 'جاري التوصيل',
            self::Unloading => 'جاري التنزيل',
            self::Received => 'تم الاستلام',
            self::Cancelled => 'ملغاة',
        };
    }

    public function isClosed(): bool
    {
        return $this === self::Received || $this === self::Cancelled;
    }

    /**
     * The stage that follows this one in the shipping flow, or null when the
     * delivery has reached a closed stage.
     */
    public function next(): ?self
    {
        $order = self::flow();

        $index = array_search($this, $order, true);

        if ($index === false || ! isset($order[$index + 1])) {
            return null;
        }

        return $order[$index + 1];
    }

    /**
     * The shipping flow in order. Cancelled is reachable from any open stage
     * but is not part of the forward progression.
     *
     * @return array<int, self>
     */
    public static function flow(): array
    {
        return [
            self::Shipping,
            self::OutForDelivery,
            self::Unloading,
            self::Received,
        ];
    }
}