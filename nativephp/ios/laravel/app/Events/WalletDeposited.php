<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletDeposited
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public WalletTransaction $transaction,
    ) {}
}
