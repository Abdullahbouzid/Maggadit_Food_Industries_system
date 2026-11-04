<?php

declare(strict_types=1);

namespace App\Enums;

enum PriceSource: string
{
    case CustomerPriceList = 'customer_price_list';
    case DefaultPriceList = 'default_price_list';
    case Base = 'base';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::CustomerPriceList => 'قائمة أسعار العميل',
            self::DefaultPriceList => 'قائمة الأسعار الافتراضية',
            self::Base => 'السعر الأساسي للمنتج',
            self::Manual => 'سعر يدوي',
        };
    }
}
