<?php

declare(strict_types=1);

namespace App\Enums;

enum TaxScope: string
{
    case Item = 'item';
    case Invoice = 'invoice';

    public function label(): string
    {
        return match ($this) {
            self::Item => 'للكمية (Item Level)',
            self::Invoice => 'لكل الفاتورة (Invoice Level)',
        };
    }
}
