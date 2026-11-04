<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ItemType;
use App\Models\Uom;
use Illuminate\Database\Seeder;

class ItemCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $itemTypes = [
            ['code' => 'FINISHED', 'name' => 'منتج نهائي', 'is_active' => true],
            ['code' => 'RAW', 'name' => 'مادة خام', 'is_active' => true],
            ['code' => 'PACKAGING', 'name' => 'تغليف', 'is_active' => true],
        ];

        foreach ($itemTypes as $typeData) {
            ItemType::updateOrCreate(['code' => $typeData['code']], $typeData);
            $this->command->info("✅ ItemType seeded: {$typeData['name']} ({$typeData['code']})");
        }

        $categories = [
            ['code' => 'CHOC', 'name' => 'شوكولاتة', 'division' => 'food', 'show_in_customer_app' => true],
            ['code' => 'SWEETS', 'name' => 'حلويات', 'division' => 'food', 'show_in_customer_app' => true],
            ['code' => 'SNACKS', 'name' => 'مقرمشات', 'division' => 'food', 'show_in_customer_app' => true],
            ['code' => 'DRINKS', 'name' => 'مشروبات', 'division' => 'food', 'show_in_customer_app' => true],
            ['code' => 'DAIRY', 'name' => 'ألبان', 'division' => 'food', 'show_in_customer_app' => true],
        ];

        foreach ($categories as $catData) {
            Category::updateOrCreate(['code' => $catData['code']], $catData);
            $this->command->info("✅ Category seeded: {$catData['name']} ({$catData['code']})");
        }

        $brands = [
            ['name' => 'مجديت', 'show_in_customer_app' => true],
            ['name' => 'الصفوة', 'show_in_customer_app' => true],
            ['name' => 'النخبة', 'show_in_customer_app' => true],
            ['name' => 'سمارت', 'show_in_customer_app' => false],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['name' => $brandData['name']],
                $brandData
            );
            $this->command->info("✅ Brand seeded: {$brandData['name']}");
        }

        $uoms = [
            ['code' => 'PCS', 'name_en' => 'Piece', 'name_ar' => 'قطعة', 'uom_symbol' => 'pc', 'is_active' => true],
            ['code' => 'KG', 'name_en' => 'Kilogram', 'name_ar' => 'كيلوجرام', 'uom_symbol' => 'kg', 'is_active' => true],
            ['code' => 'G', 'name_en' => 'Gram', 'name_ar' => 'جرام', 'uom_symbol' => 'g', 'is_active' => true],
            ['code' => 'L', 'name_en' => 'Liter', 'name_ar' => 'لتر', 'uom_symbol' => 'L', 'is_active' => true],
            ['code' => 'BOX', 'name_en' => 'Box', 'name_ar' => 'صندوق', 'uom_symbol' => 'bx', 'is_active' => true],
        ];

        foreach ($uoms as $uomData) {
            Uom::updateOrCreate(['code' => $uomData['code']], $uomData);
            $this->command->info("✅ Uom seeded: {$uomData['name_ar']} ({$uomData['code']})");
        }

        $this->command->info('✅ Total item types: ' . ItemType::count());
        $this->command->info('✅ Total categories: ' . Category::count());
        $this->command->info('✅ Total brands: ' . Brand::count());
        $this->command->info('✅ Total uoms: ' . Uom::count());
    }
}
