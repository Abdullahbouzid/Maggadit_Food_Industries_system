<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceList;
use App\Models\PriceListVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceListVersion>
 */
class PriceListVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'price_list_id' => PriceList::factory(),
            'effective_date' => now(),
            'is_active' => true,
            'created_by' => null,
            'currency_id' => null,
            'exchange_rate' => 1.0,
        ];
    }
}
