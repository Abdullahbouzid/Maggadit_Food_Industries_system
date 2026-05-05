<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\UOM;

class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        $priceLists = [
            [
                'code' => 'PL-RTL-001',
                'name' => 'قائمة أسعار التجزئة',
                'description' => 'أسعار البيع للجمهور',
                'type' => 'retail',
                'is_active' => true,
            ],
            [
                'code' => 'PL-WHL-001',
                'name' => 'قائمة أسعار الجملة',
                'description' => 'أسعار البيع بالجملة',
                'type' => 'wholesale',
                'is_active' => true,
            ],
            [
                'code' => 'PL-DIST-001',
                'name' => 'قائمة أسعار الموزعين',
                'description' => 'أسعار خاصة للموزعين',
                'type' => 'distributor',
                'is_active' => true,
            ],
        ];
        
        foreach ($priceLists as $priceList) {
            PriceList::create($priceList);
        }
        
        // إضافة أسعار للمنتجات
        $products = Product::all();
        $retailList = PriceList::where('code', 'PL-RTL-001')->first();
        $wholesaleList = PriceList::where('code', 'PL-WHL-001')->first();
        $uom = UOM::where('code', 'KG')->first();
        
        foreach ($products as $product) {
            // سعر التجزئة
            $retailList->products()->attach($product->id, [
                'uom_id' => $uom->id,
                'price' => rand(10, 50),
                'min_quantity' => 1,
                'discount_percentage' => 0,
                'valid_from' => now(),
            ]);
            
            // سعر الجملة
            $wholesaleList->products()->attach($product->id, [
                'uom_id' => $uom->id,
                'price' => rand(8, 40),
                'min_quantity' => 10,
                'discount_percentage' => 10,
                'valid_from' => now(),
            ]);
        }
    }
}