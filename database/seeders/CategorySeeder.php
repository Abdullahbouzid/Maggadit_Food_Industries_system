<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'زيوت', 'description' => 'زيوت طعام بجميع أنواعها', 'is_active' => true],
            ['name' => 'صلصات', 'description' => 'صلصات ومخللات', 'is_active' => true],
            ['name' => 'معلبات', 'description' => 'مواد غذائية معلبة', 'is_active' => true],
            ['name' => 'توابل', 'description' => 'بهارات وتوابل', 'is_active' => true],
            ['name' => 'حليب ومشتقاته', 'description' => 'منتجات الألبان', 'is_active' => true],
            ['name' => 'مشروبات', 'description' => 'عصائر ومشروبات', 'is_active' => true],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}