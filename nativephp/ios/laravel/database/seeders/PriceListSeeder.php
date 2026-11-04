<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::where('code', 'LYD')->first();
        $user = User::first();

        if (!$currency || !$user) {
            $this->command->error('❌ Currency (LYD) or User not found. Run CurrencySeeder and UserSeeder first.');
            return;
        }

        $priceLists = [
            ['name' => 'قائمة التجزئة', 'description' => 'للعملاء العاديين', 'multiplier' => 1.00, 'is_default' => true],
            ['name' => 'قائمة الجملة', 'description' => 'للتجار', 'multiplier' => 0.90, 'is_default' => false],
            ['name' => 'قائمة المطاعم', 'description' => 'للمطاعم', 'multiplier' => 0.85, 'is_default' => false],
        ];

        $discountPercents = [
            'قائمة التجزئة' => 0,
            'قائمة الجملة' => 10,
            'قائمة المطاعم' => 15,
        ];

        foreach ($priceLists as $pl) {
            $priceList = PriceList::updateOrCreate(
                ['name' => $pl['name']],
                [
                    'name' => $pl['name'],
                    'description' => $pl['description'],
                    'status' => 'active',
                    'currency_id' => $currency->id,
                    'is_default' => $pl['is_default'],
                    'created_by' => $user->id,
                ]
            );
            $this->command->info("✅ PriceList seeded: {$pl['name']} (multiplier: {$pl['multiplier']})");

            $version = PriceListVersion::updateOrCreate(
                ['price_list_id' => $priceList->id],
                [
                    'price_list_id' => $priceList->id,
                    'effective_date' => '2026-01-01',
                    'is_active' => true,
                    'currency_id' => $currency->id,
                    'exchange_rate' => 1.0,
                    'created_by' => $user->id,
                ]
            );
            $this->command->info("  └─ Version created: effective 2026-01-01");

            $items = Item::all();
            $itemPacks = ItemPack::all();

            $packMap = [];
            foreach ($items as $item) {
                foreach ($itemPacks as $pack) {
                    if ($pack->item_id === $item->id) {
                        $packMap[] = $pack;
                    }
                }
            }

            $packMap = ItemPack::whereIn('item_id', $items->pluck('id'))->get();

            $discount = $discountPercents[$pl['name']];

            Model::withoutEvents(function () use ($packMap, $priceList, $version, $pl, $discount, $user) {
                foreach ($packMap as $pack) {
                    $basePrice = $pack->price;
                    $finalPrice = round($basePrice * $pl['multiplier'], 2);

                    PriceListItem::updateOrCreate(
                        ['price_list_id' => $priceList->id, 'item_id' => $pack->item_id, 'item_pack_id' => $pack->id, 'min_quantity' => 1],
                        [
                            'price_list_id' => $priceList->id,
                            'item_id' => $pack->item_id,
                            'item_pack_id' => $pack->id,
                            'price' => $finalPrice,
                            'min_quantity' => 1,
                            'discount_percent' => $discount,
                            'is_active' => true,
                            'valid_from' => '2026-01-01',
                            'valid_to' => null,
                            'effective_date' => '2026-01-01',
                            'created_by' => $user->id,
                        ]
                    );

                    PriceListVersionItem::updateOrCreate(
                        ['price_list_version_id' => $version->id, 'item_id' => $pack->item_id, 'item_pack_id' => $pack->id],
                        [
                            'price_list_version_id' => $version->id,
                            'item_id' => $pack->item_id,
                            'item_pack_id' => $pack->id,
                            'price' => $finalPrice,
                        ]
                    );
                }

                // Wholesale: add additional tier for min_quantity >= 10
                if ($pl['name'] === 'قائمة الجملة') {
                    foreach ($packMap as $pack) {
                        $basePrice = $pack->price;
                        $tierPrice = round($basePrice * 0.855, 2);

                        PriceListItem::updateOrCreate(
                            ['price_list_id' => $priceList->id, 'item_id' => $pack->item_id, 'item_pack_id' => $pack->id, 'min_quantity' => 10],
                            [
                                'price_list_id' => $priceList->id,
                                'item_id' => $pack->item_id,
                                'item_pack_id' => $pack->id,
                                'price' => $tierPrice,
                                'min_quantity' => 10,
                                'discount_percent' => 15,
                                'is_active' => true,
                                'valid_from' => '2026-01-01',
                                'valid_to' => null,
                                'effective_date' => '2026-01-01',
                                'created_by' => $user->id,
                            ]
                        );
                    }
                }
            });

            if ($pl['name'] === 'قائمة الجملة') {
                $this->command->info("  └─ Added wholesale tier for min_quantity >= 10");
            }
        }

        $this->command->info('✅ Total price lists: ' . PriceList::count());
        $this->command->info('✅ Total price list items: ' . PriceListItem::count());
        $this->command->info('✅ Total price list versions: ' . PriceListVersion::count());
    }
}
