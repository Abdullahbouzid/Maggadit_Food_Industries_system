<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemPack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'ITEM-' . fake()->unique()->randomNumber('4'),
            'name_en' => fake()->word(),
            'name_ar' => 'منتج ' . fake()->word(),
            'item_type_id' => null,
            'category_id' => null,
            'is_inactive' => false,
            'is_new' => false,
            'is_price_protected' => false,
        ];
    }
}

/**
 * @extends Factory<ItemPack>
 */
class ItemPackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'uom_id' => 1,
            'quantity_in_pack' => 1,
            'barcode' => fake()->unique()->ean13(),
            'is_default_pack' => true,
            'sort_order' => 0,
            'price' => 100.00,
        ];
    }

    public function nonDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default_pack' => false,
        ]);
    }
}
