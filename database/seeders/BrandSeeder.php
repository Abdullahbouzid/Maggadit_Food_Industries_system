<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'مجديت', 'description' => 'العلامة التجارية الرئيسية', 'is_active' => true],
            ['name' => 'الريف', 'description' => 'منتجات طبيعية', 'is_active' => true],
            ['name' => 'الوادي', 'description' => 'منتجات غذائية', 'is_active' => true],
            ['name' => 'الأصيل', 'description' => 'منتجات فاخرة', 'is_active' => true],
        ];
        
        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}