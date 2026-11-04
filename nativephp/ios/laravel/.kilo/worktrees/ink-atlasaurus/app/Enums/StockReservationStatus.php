<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status of a StockReservation record.
 * Matches the check constraint: 'active', 'consumed', 'released', 'expired'
 */
enum StockReservationStatus: string
{
    case Active = 'active';
    case Consumed = 'consumed';
    case Released = 'released';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Consumed => 'مستهلك',
            self::Released => 'تم إطلاقه',
            self::Expired => 'منتهي الصلاحية',
        };
    }
}
