<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceListItem;
use App\Models\PriceList;
use App\Models\Item;
use App\Models\ItemPack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceListItem>
 */
class PriceListItemFactory extends Factory
{
    public function definition(): array
    {
        $priceList = PriceList::factory();
        $item = Item::factory();
        $pack = ItemPack::factory()->for($item);

        return [
            'price_list_id' => $priceList,
            'item_id' => $item,
            'item_pack_id' => $pack,
            'price' => fake()->randomFloat(2, 50, 500),
            'min_quantity' => 1,
            'discount_percent' => 0,
            'is_active' => true,
            'valid_from' => null,
            'valid_to' => null,
        ];
    }

    public function withTier(float $minQuantity, float $price, float $discountPercent = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'min_quantity' => $minQuantity,
            'price' => $price,
            'discount_percent' => $discountPercent,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
