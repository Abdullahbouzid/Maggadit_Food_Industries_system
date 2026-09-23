<?php
declare(strict_types=1);

namespace App\Enums;

enum DeliveryStage: string
{
    case Shipping = 'shipping';
    case OutForDelivery = 'out_for_delivery';
    case Unloading = 'unloading';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Shipping => 'جاري الشحن',
            self::OutForDelivery => 'جاري التوصيل',
            self::Unloading => 'جاري التنزيل',
            self::Received => 'تم الاستلام',
        };
    }
}