<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceList;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use Illuminate\Database\Seeder;

class DemoPriceListProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'CHK-001', 'name_ar' => 'دجاج طازج', 'name_en' => 'Fresh Chicken', 'category_id' => 2, 'brand_id' => 1],
            ['code' => 'RCE-001', 'name_ar' => 'أرز بسمتي', 'name_en' => 'Basmati Rice', 'category_id' => 2, 'brand_id' => 2],
            ['code' => 'OLV-001', 'name_ar' => 'زيت زيتون', 'name_en' => 'Olive Oil', 'category_id' => 4, 'brand_id' => 3],
            ['code' => 'MLK-001', 'name_ar' => 'حليب طازج', 'name_en' => 'Fresh Milk', 'category_id' => 2, 'brand_id' => 1],
            ['code' => 'HNY-001', 'name_ar' => 'عسل طبيعي', 'name_en' => 'Natural Honey', 'category_id' => 5, 'brand_id' => 2],
        ];

        $packConfigs = [
            [
                ['name' => 'عبوة صغيرة', 'uom_id' => 3, 'price' => 15, 'qty' => 100, 'barcode' => 'CHK001-S'],
                ['name' => 'عبوة متوسطة', 'uom_id' => 2, 'price' => 45, 'qty' => 50, 'barcode' => 'CHK001-M'],
                ['name' => 'عبوة كبيرة', 'uom_id' => 2, 'price' => 120, 'qty' => 20, 'barcode' => 'CHK001-L'],
            ],
            [
                ['name' => 'كيس 1kg', 'uom_id' => 2, 'price' => 25, 'qty' => 200, 'barcode' => 'RCE001-1'],
                ['name' => 'كيس 5kg', 'uom_id' => 2, 'price' => 110, 'qty' => 80, 'barcode' => 'RCE001-5'],
                ['name' => 'كيس 25kg', 'uom_id' => 2, 'price' => 500, 'qty' => 30, 'barcode' => 'RCE001-25'],
            ],
            [
                ['name' => 'زجاجة 500ml', 'uom_id' => 3, 'price' => 30, 'qty' => 150, 'barcode' => 'OLV001-500'],
                ['name' => 'زجاجة 1L', 'uom_id' => 2, 'price' => 55, 'qty' => 100, 'barcode' => 'OLV001-1L'],
                ['name' => 'كاني 5L', 'uom_id' => 2, 'price' => 250, 'qty' => 40, 'barcode' => 'OLV001-5L'],
            ],
            [
                ['name' => 'عبوة 1L', 'uom_id' => 2, 'price' => 12, 'qty' => 300, 'barcode' => 'MLK001-1'],
                ['name' => 'عبوة 2L', 'uom_id' => 2, 'price' => 22, 'qty' => 150, 'barcode' => 'MLK001-2'],
                ['name' => 'عبوة 5L', 'uom_id' => 2, 'price' => 50, 'qty' => 60, 'barcode' => 'MLK001-5'],
            ],
            [
                ['name' => 'علبة 500g', 'uom_id' => 3, 'price' => 60, 'qty' => 80, 'barcode' => 'HNY001-500'],
                ['name' => 'علبة 1kg', 'uom_id' => 2, 'price' => 110, 'qty' => 50, 'barcode' => 'HNY001-1'],
                ['name' => 'علبة 5kg', 'uom_id' => 2, 'price' => 500, 'qty' => 15, 'barcode' => 'HNY001-5'],
            ],
        ];

        $warehouses = [7, 8];
        $allPackIds = [];

        foreach ($products as $i => $prod) {
            $item = Item::create($prod);
            echo "Created item: {$item->name_ar} (ID: {$item->id})\n";

            foreach ($packConfigs[$i] as $pack) {
                $itemPack = ItemPack::create([
                    'item_id' => $item->id,
                    'uom_id' => $pack['uom_id'],
                    'quantity' => $pack['qty'],
                    'barcode' => $pack['barcode'],
                    'is_default_pack' => false,
                ]);

                $allPackIds[] = ['item_id' => $item->id, 'pack_id' => $itemPack->id, 'price' => $pack['price']];

                echo "  Pack: {$pack['name']} (UoM: {$pack['uom_id']}, Price: {$pack['price']})\n";
            }
        }

        // Create Cash price list
        $priceList = PriceList::firstOrCreate(
            ['name' => 'كاش'],
            ['description' => 'الدفع كاش كامل', 'status' => 'active', 'created_by' => 1]
        );
        echo "\nPrice List: {$priceList->name} (ID: {$priceList->id})\n";

        $version = PriceListVersion::create([
            'price_list_id' => $priceList->id,
            'effective_date' => now()->toDateString(),
            'is_active' => true,
            'created_by' => 1,
        ]);
        echo "Version: {$version->effective_date} (ID: {$version->id})\n";

        foreach ($allPackIds as $p) {
            PriceListVersionItem::create([
                'price_list_version_id' => $version->id,
                'item_id' => $p['item_id'],
                'item_pack_id' => $p['pack_id'],
                'price' => $p['price'],
            ]);
        }
        echo "Added " . count($allPackIds) . " price entries to version.\n";
        echo "\nDone!\n";
    }
}
