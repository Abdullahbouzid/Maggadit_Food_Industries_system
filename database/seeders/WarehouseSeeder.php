<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'WH-001',
                'name' => 'المخزن الرئيسي - الرياض',
                'address' => 'الرياض - المنطقة الصناعية',
                'phone' => '0112345678',
                'manager_name' => 'أحمد محمد',
                'is_active' => true,
            ],
            [
                'code' => 'WH-002',
                'name' => 'مخزن جدة',
                'address' => 'جدة - الميناء',
                'phone' => '0123456789',
                'manager_name' => 'خالد علي',
                'is_active' => true,
            ],
            [
                'code' => 'WH-003',
                'name' => 'مخزن الدمام',
                'address' => 'الدمام - المنطقة الصناعية الثانية',
                'phone' => '0134567890',
                'manager_name' => 'سعد عبدالله',
                'is_active' => true,
            ],
        ];
        
        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}