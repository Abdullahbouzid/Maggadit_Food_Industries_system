<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\UOM;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $uom = UOM::where('code', 'KG')->first();
        
        $products = [
            [
                'sku' => 'PRD-001',
                'name' => 'زيت زيتون بكر',
                'description' => 'زيت زيتون بكر ممتاز',
                'technical_specs' => 'درجة حموضة أقل من 0.8%',
                'barcode' => '1234567890123',
                'weight' => 1.000,
                'length' => 15,
                'width' => 10,
                'height' => 25,
                'is_active' => true,
                'is_available_for_order' => true,
            ],
            [
                'sku' => 'PRD-002',
                'name' => 'صلصة طماطم',
                'description' => 'صلصة طماطم مركزة',
                'technical_specs' => 'تركيز 28%',
                'barcode' => '1234567890124',
                'weight' => 0.500,
                'length' => 8,
                'width' => 8,
                'height' => 12,
                'is_active' => true,
                'is_available_for_order' => true,
            ],
            [
                'sku' => 'PRD-003',
                'name' => 'فول مدمس معلب',
                'description' => 'فول مدمس جاهز',
                'technical_specs' => 'وزن صافي 400 جرام',
                'barcode' => '1234567890125',
                'weight' => 0.400,
                'length' => 6,
                'width' => 6,
                'height' => 4,
                'is_active' => true,
                'is_available_for_order' => true,
            ],
        ];
        
        foreach ($products as $index => $productData) {
            $product = Product::create(array_merge($productData, [
                'category_id' => $categories[$index % count($categories)]->id,
                'brand_id' => $brands[$index % count($brands)]->id,
                'default_uom_id' => $uom->id,
            ]));
        }
    }
}