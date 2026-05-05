<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UOM;

class UOMSeeder extends Seeder
{
    public function run(): void
    {
        $uoms = [
            ['code' => 'KG', 'name' => 'كيلوجرام', 'symbol' => 'كجم', 'conversion_factor' => 1, 'base_uom_id' => null, 'is_active' => true],
            ['code' => 'GM', 'name' => 'جرام', 'symbol' => 'جم', 'conversion_factor' => 0.001, 'base_uom_id' => 1, 'is_active' => true],
            ['code' => 'PC', 'name' => 'قطعة', 'symbol' => 'قطعة', 'conversion_factor' => 1, 'base_uom_id' => null, 'is_active' => true],
            ['code' => 'BOX', 'name' => 'صندوق', 'symbol' => 'صندوق', 'conversion_factor' => 1, 'base_uom_id' => null, 'is_active' => true],
            ['code' => 'LTR', 'name' => 'لتر', 'symbol' => 'لتر', 'conversion_factor' => 1, 'base_uom_id' => null, 'is_active' => true],
            ['code' => 'ML', 'name' => 'ملليلتر', 'symbol' => 'مل', 'conversion_factor' => 0.001, 'base_uom_id' => 5, 'is_active' => true],
        ];
        
        foreach ($uoms as $uom) {
            UOM::create($uom);
        }
    }
}