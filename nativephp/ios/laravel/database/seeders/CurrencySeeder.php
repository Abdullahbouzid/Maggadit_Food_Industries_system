<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'دينار ليبي',
                'code' => 'LYD',
                'coin_description' => 'دينار',
                'base_currency' => true,
                'exchange_rate_to_base' => 1.0,
            ],
            [
                'name' => 'دولار أمريكي',
                'code' => 'USD',
                'coin_description' => 'دولار',
                'base_currency' => false,
                'exchange_rate_to_base' => 0.21,
            ],
            [
                'name' => 'يورو',
                'code' => 'EUR',
                'coin_description' => 'يورو',
                'base_currency' => false,
                'exchange_rate_to_base' => 0.18,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
            $this->command->info("✅ Currency seeded: {$currency['name']} ({$currency['code']})");
        }

        $this->command->info('✅ Total currencies: ' . Currency::count());
    }
}
