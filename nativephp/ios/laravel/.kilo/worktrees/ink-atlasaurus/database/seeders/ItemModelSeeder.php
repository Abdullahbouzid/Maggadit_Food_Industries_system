<?php

namespace Database\Seeders;

use App\Models\ItemModel;
use Illuminate\Database\Seeder;

class ItemModelSeeder extends Seeder
{
    public function run(): void
    {
        $itemModels = [
            ['code' => 'STD', 'name_en' => 'Standard', 'name_ar' => 'قياسي'],
            ['code' => 'PREM', 'name_en' => 'Premium', 'name_ar' => 'ممتاز'],
            ['code' => 'ECON', 'name_en' => 'Economy', 'name_ar' => 'اقتصادي'],
            ['code' => 'ORG', 'name_en' => 'Organic', 'name_ar' => 'عضوي'],
            ['code' => 'SUGARFREE', 'name_en' => 'Sugar-Free', 'name_ar' => 'بدون سكر'],
        ];

        foreach ($itemModels as $modelData) {
            ItemModel::updateOrCreate(
                ['code' => $modelData['code']],
                array_merge($modelData, ['is_active' => true])
            );
            $this->command->info("✅ ItemModel seeded: {$modelData['name_ar']} ({$modelData['code']})");
        }

        $this->command->info('✅ Total item models: ' . ItemModel::count());
    }
}
