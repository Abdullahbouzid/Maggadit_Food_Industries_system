<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Reserve = 'reserve';
    case Release = 'release';
    case Adjustment = 'adjustment';
    case Damage = 'damage';
    case ReturnGood = 'return_good';
    case ReturnDamaged = 'return_damaged';

    public function label(): string
    {
        return match ($this) {
            self::In => 'استلام',
            self::Out => 'صرف',
            self::Reserve => 'حجز',
            self::Release => 'إطلاق حجز',
            self::Adjustment => 'تعديل',
            self::Damage => 'تلف',
            self::ReturnGood => 'مرتجع جيد',
            self::ReturnDamaged => 'مرتجع تالف',
        };
    }
}
