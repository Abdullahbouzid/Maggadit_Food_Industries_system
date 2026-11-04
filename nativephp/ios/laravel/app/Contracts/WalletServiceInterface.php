<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerWallet;
use App\Models\Invoice;
use App\Models\WalletTransaction;

interface WalletServiceInterface
{
    public function deposit(
        Customer $customer,
        float $amount,
        ?string $method = null,
        ?string $referenceNumber = null,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction;

    public function debit(
        Customer $customer,
        float $amount,
        Invoice $invoice,
        ?string $notes = null,
        ?int $userId = null,
    ): WalletTransaction;

    public function refund(
        Customer $customer,
        float $amount,
        CreditNote $creditNote,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction;

    public function adjust(
        Customer $customer,
        float $newBalance,
        string $reason,
        int $userId,
    ): WalletTransaction;

    public function getBalance(Customer $customer, int $currencyId): float;

    public function getOrCreateWallet(Customer $customer): CustomerWallet;
}
