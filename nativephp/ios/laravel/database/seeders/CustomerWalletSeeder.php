<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerWallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CustomerWalletSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::where('code', 'LYD')->first();
        // created_by is a FK to users; user ids are not guaranteed to start at 1.
        $admin = User::orderBy('id')->first();
        $createdBy = $admin?->id;

        if (!$currency) {
            $this->command->error('❌ Currency (LYD) not found. Run CurrencySeeder first.');
            return;
        }

        $walletBalances = [
            'cust002' => 2000.00,
            'cust004' => 500.00,
            'cust005' => 3000.00,
            'cust007' => 1000.00,
            'cust009' => 1500.00,
            'cust010' => 800.00,
        ];

        $creditCustomers = Customer::where('type', 'credit')->get();

        Model::withoutEvents(function () use ($creditCustomers, $currency, $createdBy, $walletBalances) {
            foreach ($creditCustomers as $customer) {
                $balance = $walletBalances[$customer->username] ?? 0.00;

                $wallet = CustomerWallet::updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'customer_id' => $customer->id,
                        'currency_id' => $currency->id,
                        'balance' => $balance,
                        'total_deposited' => $balance,
                        'total_spent' => 0,
                        'is_active' => true,
                    ]
                );

                WalletTransaction::updateOrCreate(
                    ['wallet_id' => $wallet->id, 'reference_number' => 'INIT-DEPOSIT'],
                    [
                        'wallet_id' => $wallet->id,
                        'type' => 'deposit',
                        'amount' => $balance,
                        'balance_after' => $balance,
                        'currency_id' => $currency->id,
                        'exchange_rate' => 1.0,
                        'reference_type' => null,
                        'reference_id' => null,
                        'method' => 'bank_transfer',
                        'reference_number' => 'INIT-DEPOSIT',
                        'created_by' => $createdBy,
                        'notes' => 'رصيد ابتدائي عند إعداد النظام',
                    ]
                );
            }
        });

        foreach ($creditCustomers as $customer) {
            $this->command->info("✅ Wallet seeded: {$customer->name}");
        }

        $this->command->info('✅ Total customer wallets: ' . CustomerWallet::count());
        $this->command->info('✅ Total wallet transactions: ' . WalletTransaction::count());
    }
}
