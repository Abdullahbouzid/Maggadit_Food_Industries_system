<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->currencyCode(),
            'code' => fake()->currencyCode(),
            'coin_description' => fake()->word(),
            'base_currency' => false,
            'exchange_rate_to_base' => null,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_currency' => true,
            'exchange_rate_to_base' => 1.0,
        ]);
    }
}
