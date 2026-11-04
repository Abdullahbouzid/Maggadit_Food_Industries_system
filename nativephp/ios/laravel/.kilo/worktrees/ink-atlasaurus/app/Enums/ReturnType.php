<?php
declare(strict_types=1);

namespace App\Enums;

enum ReturnType: string
{
    case Full = 'full';
    case Partial = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::Full => 'استرجاع كامل',
            self::Partial => 'استرجاع جزئي',
        };
    }
}
