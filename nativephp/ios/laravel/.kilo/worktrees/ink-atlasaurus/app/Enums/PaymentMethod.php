<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Online = 'online';
    case Wallet = 'wallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::BankTransfer => 'تحويل بنكي',
            self::Cheque => 'شيك',
            self::Online => 'إلكتروني',
            self::Wallet => 'محفظة',
        };
    }
}
