<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WalletServiceInterface;
use App\Enums\WalletTransactionType;
use App\Enums\PaymentMethod;
use App\Events\WalletDebited;
use App\Events\WalletDeposited;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\CreditLimitExceededException;
use App\Helpers\UnitOfWork;
use App\Models\AuditLog;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerWallet;
use App\Models\Invoice;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService implements WalletServiceInterface
{
    public function deposit(
        Customer $customer,
        float $amount,
        ?string $method = null,
        ?string $referenceNumber = null,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction {
        return UnitOfWork::transactional(function ($registerEvent) use (
            $customer, $amount, $method, $referenceNumber, $userId, $notes
        ) {
            $wallet = $this->getOrCreateWallet($customer);

            $wallet->increment('balance', $amount);
            $wallet->increment('total_deposited', $amount);
            $wallet->refresh();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::Deposit->value,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'currency_id' => $wallet->currency_id,
                'exchange_rate' => 1.0,
                'reference_type' => null,
                'reference_id' => null,
                'method' => $method,
                'reference_number' => $referenceNumber,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            $this->logAudit($wallet, 'deposit', null, $transaction);

            $registerEvent(new WalletDeposited($customer, $transaction));

            return $transaction;
        });
    }

    public function debit(
        Customer $customer,
        float $amount,
        Invoice $invoice,
        ?string $notes = null,
        ?int $userId = null,
    ): WalletTransaction {
        return UnitOfWork::transactional(function ($registerEvent) use (
            $customer, $amount, $invoice, $notes, $userId
        ) {
            $wallet = $this->getOrCreateWallet($customer);
            $currentBalance = (float) $wallet->balance;

            if ($currentBalance < $amount && $customer->type !== 'credit') {
                throw new InsufficientBalanceException(
                    required: $amount,
                    available: $currentBalance,
                    currencyCode: $wallet->currency->code ?? 'USD',
                );
            }

            if ($customer->type === 'credit' && $currentBalance < $amount) {
                $newBalance = $currentBalance + (float) $customer->credit_limit - $amount;
                if ($newBalance < 0) {
                    throw new CreditLimitExceededException(
                        required: $amount,
                        limit: (float) $customer->credit_limit,
                        currentBalance: $currentBalance,
                    );
                }
            }

            $wallet->decrement('balance', $amount);
            $wallet->increment('total_spent', $amount);
            $wallet->refresh();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::Purchase->value,
                'amount' => -$amount,
                'balance_after' => $wallet->balance,
                'currency_id' => $wallet->currency_id,
                'exchange_rate' => 1.0,
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'method' => 'wallet',
                'reference_number' => null,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            $this->logAudit($wallet, 'debit', null, $transaction);

            $registerEvent(new WalletDebited($customer, $transaction));

            return $transaction;
        });
    }

    public function refund(
        Customer $customer,
        float $amount,
        CreditNote $creditNote,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction {
        return UnitOfWork::transactional(function () use (
            $customer, $amount, $creditNote, $userId, $notes
        ) {
            $wallet = $this->getOrCreateWallet($customer);

            $wallet->increment('balance', $amount);
            $wallet->refresh();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::Refund->value,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'currency_id' => $wallet->currency_id,
                'exchange_rate' => 1.0,
                'reference_type' => CreditNote::class,
                'reference_id' => $creditNote->id,
                'method' => null,
                'reference_number' => $creditNote->credit_note_number,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            $this->logAudit($wallet, 'refund', null, $transaction);

            return $transaction;
        });
    }

    public function adjust(
        Customer $customer,
        float $newBalance,
        string $reason,
        int $userId,
    ): WalletTransaction {
        return UnitOfWork::transactional(function () use (
            $customer, $newBalance, $reason, $userId
        ) {
            $wallet = $this->getOrCreateWallet($customer);

            $currentBalance = (float) $wallet->balance;
            $difference = $newBalance - $currentBalance;

            $wallet->update(['balance' => $newBalance]);
            $wallet->refresh();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransactionType::Adjustment->value,
                'amount' => $difference,
                'balance_after' => $wallet->balance,
                'currency_id' => $wallet->currency_id,
                'exchange_rate' => 1.0,
                'reference_type' => null,
                'reference_id' => null,
                'method' => null,
                'reference_number' => null,
                'created_by' => $userId,
                'notes' => 'Adjustment: '.$reason,
            ]);

            $this->logAudit($wallet, 'adjust', ['old_balance' => $currentBalance], $transaction);

            return $transaction;
        });
    }

    public function getBalance(Customer $customer, int $currencyId): float
    {
        $wallet = CustomerWallet::where('customer_id', $customer->id)
            ->where('currency_id', $currencyId)
            ->first();

        return $wallet ? (float) $wallet->balance : 0.0;
    }

    public function getOrCreateWallet(Customer $customer): CustomerWallet
    {
        $wallet = CustomerWallet::where('customer_id', $customer->id)->first();

        if (!$wallet && $customer->wallet_enabled) {
            $currency = \App\Models\Currency::where('base_currency', true)->first();

            $wallet = CustomerWallet::create([
                'customer_id' => $customer->id,
                'currency_id' => $currency?->id ?? 1,
                'balance' => 0,
                'total_deposited' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);
        }

        if (!$wallet) {
            throw new \Exception('Customer wallet is not enabled or does not exist.');
        }

        return $wallet;
    }

    public function hasSufficientBalance(Customer $customer, float $amount, int $currencyId): bool
    {
        $balance = $this->getBalance($customer, $currencyId);

        if ($balance >= $amount) {
            return true;
        }

        return $customer->type === 'credit'
            && $balance + (float) $customer->credit_limit >= $amount;
    }

    private function logAudit(CustomerWallet $wallet, string $event, ?array $oldValues, $newValue): void
    {
        $newValueArray = $newValue instanceof \Illuminate\Database\Eloquent\Model
            ? $newValue->toArray()
            : ['value' => $newValue];

        AuditLog::create([
            'auditable_type' => CustomerWallet::class,
            'auditable_id' => $wallet->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValueArray,
            'user_id' => $newValueArray['created_by'] ?? null,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
