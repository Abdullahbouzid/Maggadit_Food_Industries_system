<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemModel;
use App\Models\ItemType;
use App\Models\ItemPack;
use App\Models\Warehouse;
use App\Models\PackType;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $finishedType = ItemType::where('code', 'FINISHED')->first();
        $warehouse = Warehouse::where('code', 'WH-TR-01')->first();
        $singlePackType = PackType::where('code', 'SMALL_BOX')->first();
        $singlePack = PackType::where('code', 'SINGLE')->first();

        if (!$finishedType || !$warehouse || !$singlePack || !$singlePackType) {
            $this->command->error('❌ Required references not found. Run ItemCatalogSeeder and WarehouseSeeder first.');
            return;
        }

        $categories = Category::pluck('id', 'code')->toArray();
        $brands = Brand::pluck('id', 'name')->toArray();
        $itemModels = ItemModel::pluck('id', 'code')->toArray();

        $items = [
            ['code' => 'ITM-001', 'name_ar' => 'شوكولاتة بالحليب', 'name_en' => 'Milk Chocolate', 'category' => 'CHOC', 'brand' => 'مجديت', 'model' => 'STD', 'base_price' => 5.00],
            ['code' => 'ITM-002', 'name_ar' => 'شوكولاتة داكنة', 'name_en' => 'Dark Chocolate', 'category' => 'CHOC', 'brand' => 'مجديت', 'model' => 'PREM', 'base_price' => 8.00],
            ['code' => 'ITM-003', 'name_ar' => 'شوكولاتة بالبندق', 'name_en' => 'Hazelnut Chocolate', 'category' => 'CHOC', 'brand' => 'مجديت', 'model' => 'PREM', 'base_price' => 10.00],
            ['code' => 'ITM-004', 'name_ar' => 'بسكويت بالشوكولاتة', 'name_en' => 'Chocolate Biscuit', 'category' => 'SWEETS', 'brand' => 'الصفوة', 'model' => 'STD', 'base_price' => 3.00],
            ['code' => 'ITM-005', 'name_ar' => 'كيك بالفانيليا', 'name_en' => 'Vanilla Cake', 'category' => 'SWEETS', 'brand' => 'الصفوة', 'model' => 'STD', 'base_price' => 4.00],
            ['code' => 'ITM-006', 'name_ar' => 'شيبس بالملح', 'name_en' => 'Salted Chips', 'category' => 'SNACKS', 'brand' => 'سمارت', 'model' => 'ECON', 'base_price' => 2.00],
            ['code' => 'ITM-007', 'name_ar' => 'شيبس بالجبن', 'name_en' => 'Cheese Chips', 'category' => 'SNACKS', 'brand' => 'سمارت', 'model' => 'ECON', 'base_price' => 2.50],
            ['code' => 'ITM-008', 'name_ar' => 'عصير برتقال', 'name_en' => 'Orange Juice', 'category' => 'DRINKS', 'brand' => 'النخبة', 'model' => 'STD', 'base_price' => 3.50],
            ['code' => 'ITM-009', 'name_ar' => 'عصير تفاح', 'name_en' => 'Apple Juice', 'category' => 'DRINKS', 'brand' => 'النخبة', 'model' => 'STD', 'base_price' => 3.50],
            ['code' => 'ITM-010', 'name_ar' => 'لبن طازج', 'name_en' => 'Fresh Milk', 'category' => 'DAIRY', 'brand' => 'الصفوة', 'model' => 'ORG', 'base_price' => 1.50],
        ];

        foreach ($items as $itemData) {
            $item = Item::updateOrCreate(
                ['code' => $itemData['code']],
                [
                    'code' => $itemData['code'],
                    'name_en' => $itemData['name_en'],
                    'name_ar' => $itemData['name_ar'],
                    'item_type_id' => $finishedType->id,
                    'category_id' => $categories[$itemData['category']],
                    'brand_id' => $brands[$itemData['brand']],
                    'model_id' => $itemModels[$itemData['model']],
                    'shelf_life_time' => 180,
                    'low_stock_qty' => 20,
                    'is_inactive' => false,
                ]
            );
            $this->command->info("✅ Item seeded: {$itemData['name_ar']} ({$itemData['code']})");

            // SINGLE pack
            $singleBarcode = 'BC-' . $itemData['code'] . '-001';
            ItemPack::updateOrCreate(
                ['barcode' => $singleBarcode],
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'pack_type_id' => $singlePack->id,
                    'quantity_in_pack' => 1,
                    'barcode' => $singleBarcode,
                    'price' => $itemData['base_price'],
                    'is_default_pack' => true,
                    'sort_order' => 1,
                ]
            );
            $this->command->info("  └─ Pack: SINGLE - {$itemData['base_price']} (default)");

            // SMALL_BOX pack
            $smallBoxBarcode = 'BC-' . $itemData['code'] . '-002';
            $smallBoxPrice = round($itemData['base_price'] * 12 * 0.92, 2);
            ItemPack::updateOrCreate(
                ['barcode' => $smallBoxBarcode],
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'pack_type_id' => $singlePackType->id,
                    'quantity_in_pack' => 12,
                    'barcode' => $smallBoxBarcode,
                    'price' => $smallBoxPrice,
                    'is_default_pack' => false,
                    'sort_order' => 2,
                ]
            );
            $this->command->info("  └─ Pack: SMALL_BOX - {$smallBoxPrice}");
        }

        // ItemImages: يمكن إضافتها لاحقاً عند رفع الصور.
        $this->command->info('✅ Total items: ' . Item::count());
        $this->command->info('✅ Total item packs: ' . ItemPack::count());
    }
}
