<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case Purchase = 'purchase';
    case Refund = 'refund';
    case Withdrawal = 'withdrawal';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'إيداع',
            self::Purchase => 'شراء',
            self::Refund => 'استرجاع',
            self::Withdrawal => 'سحب',
            self::Adjustment => 'تعديل',
        };
    }
}
