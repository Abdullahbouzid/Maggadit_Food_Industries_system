<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Organization;
use App\Models\State;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'MAIN', 'name_en' => 'Main Warehouse', 'name_ar' => 'مستودع رئيسي', 'description' => 'المستودع الرئيسي'],
            ['code' => 'COLD', 'name_en' => 'Cold Storage', 'name_ar' => 'مستودع مبرد', 'description' => 'مستودع تبريد'],
            ['code' => 'DRY', 'name_en' => 'Dry Storage', 'name_ar' => 'مستودع جاف', 'description' => 'مستودع جاف'],
        ];

        foreach ($types as $typeData) {
            WarehouseType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
            $this->command->info("✅ WarehouseType seeded: {$typeData['name_ar']} ({$typeData['code']})");
        }

        $organization = Organization::where('code', 'MAGGADIT')->first();
        $libya = Country::where('code', 'LY')->first();

        if (!$organization || !$libya) {
            $this->command->error('❌ Organization or Country not found. Run OrganizationSeeder and CountryStateCitySeeder first.');
            return;
        }

        $mainType = WarehouseType::where('code', 'MAIN')->first();
        $coldType = WarehouseType::where('code', 'COLD')->first();
        $dryType = WarehouseType::where('code', 'DRY')->first();

        $warehouses = [
            [
                'code' => 'WH-TR-01',
                'name_en' => 'Tripoli Main',
                'name_ar' => 'المستودع الرئيسي طرابلس',
                'warehouse_type_id' => $mainType->id,
                'city_name' => 'طرابلس المركز',
                'phone' => '+218911111111',
            ],
            [
                'code' => 'WH-BN-01',
                'name_en' => 'Benghazi Warehouse',
                'name_ar' => 'مستودع بنغازي',
                'warehouse_type_id' => $dryType->id,
                'city_name' => 'بنغازي المركز',
                'phone' => '+218911111111',
            ],
            [
                'code' => 'WH-MS-01',
                'name_en' => 'Misrata Warehouse',
                'name_ar' => 'مستودع مصراتة',
                'warehouse_type_id' => $coldType->id,
                'city_name' => 'مصراتة المركز',
                'phone' => '+218911111111',
            ],
        ];

        foreach ($warehouses as $wh) {
            $city = City::where('name', $wh['city_name'])->first();
            if (!$city) {
                $this->command->error("❌ City not found: {$wh['city_name']}");
                continue;
            }

            $state = State::find($city->state_id);

            Warehouse::updateOrCreate(
                ['code' => $wh['code']],
                [
                    'code' => $wh['code'],
                    'name_en' => $wh['name_en'],
                    'name_ar' => $wh['name_ar'],
                    'warehouse_type_id' => $wh['warehouse_type_id'],
                    'organization_id' => $organization->id,
                    'phone' => $wh['phone'],
                    'address_ar' => $wh['name_ar'] . '، ' . $city->name,
                    'address_en' => $wh['name_en'],
                    'gps_latitude' => $city->latitude,
                    'gps_longitude' => $city->longitude,
                    'country_id' => $libya->id,
                    'state_id' => $city->state_id,
                    'city_id' => $city->id,
                    'is_active' => true,
                ]
            );
            $this->command->info("✅ Warehouse seeded: {$wh['name_ar']} ({$wh['code']})");
        }

        $this->command->info('✅ Total warehouse types: ' . WarehouseType::count());
        $this->command->info('✅ Total warehouses: ' . Warehouse::count());
    }
}
