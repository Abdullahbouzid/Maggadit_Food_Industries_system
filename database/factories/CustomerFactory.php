<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'fcm_token' => null,
            'active_organization_id' => null,
            'price_list_id' => null,
            'customer_type' => 'cash',
            'credit_limit' => 0,
            'current_balance' => 0,
            'payment_terms_days' => 0,
            'wallet_enabled' => false,
        ];
    }
}

/**
 * @extends Factory<PriceList>
 */
class PriceListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Price List ' . fake()->word(),
            'description' => fake()->sentence(),
            'status' => 'active',
            'created_by' => null,
            'is_default' => false,
            'currency_id' => null,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}

/**
 * @extends Factory<\App\Models\CustomerPriceList>
 */
class CustomerPriceListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'price_list_id' => PriceList::factory(),
            'assigned_at' => now(),
            'assigned_by' => null,
            'unassigned_at' => null,
            'is_active' => true,
        ];
    }
}
