<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentType: string
{
    case Cash = 'cash';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::Credit => 'آجل',
        };
    }
}
