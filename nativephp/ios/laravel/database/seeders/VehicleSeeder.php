<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'SMALL_TRUCK', 'name_en' => 'Small Truck', 'name_ar' => 'شاحنة صغيرة', 'description' => 'شاحنة صغيرة'],
            ['code' => 'LARGE_TRUCK', 'name_en' => 'Large Truck', 'name_ar' => 'شاحنة كبيرة', 'description' => 'شاحنة كبيرة'],
            ['code' => 'VAN', 'name_en' => 'Delivery Van', 'name_ar' => 'سيارة توصيل', 'description' => 'سيارة توصيل'],
        ];

        foreach ($types as $typeData) {
            VehicleType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
            $this->command->info("✅ VehicleType seeded: {$typeData['name_ar']} ({$typeData['code']})");
        }

        $organization = Organization::where('code', 'MAGGADIT')->first();
        if (!$organization) {
            $this->command->error('❌ Organization not found. Run OrganizationSeeder first.');
            return;
        }

        $smallTruck = VehicleType::where('code', 'SMALL_TRUCK')->first();
        $largeTruck = VehicleType::where('code', 'LARGE_TRUCK')->first();
        $van = VehicleType::where('code', 'VAN')->first();

        $vehicles = [
            [
                'vehicle_name' => 'شاحنة 1',
                'vehicle_code' => 'VH-001',
                'plate_no' => '5-12345',
                'vehicle_type_id' => $smallTruck->id,
                'capacity' => 500.00,
            ],
            [
                'vehicle_name' => 'شاحنة 2',
                'vehicle_code' => 'VH-002',
                'plate_no' => '5-12346',
                'vehicle_type_id' => $largeTruck->id,
                'capacity' => 1500.00,
            ],
            [
                'vehicle_name' => 'سيارة توصيل 1',
                'vehicle_code' => 'VH-003',
                'plate_no' => '5-12347',
                'vehicle_type_id' => $van->id,
                'capacity' => 200.00,
            ],
            [
                'vehicle_name' => 'سيارة توصيل 2',
                'vehicle_code' => 'VH-004',
                'plate_no' => '5-12348',
                'vehicle_type_id' => $van->id,
                'capacity' => 200.00,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['vehicle_code' => $vehicle['vehicle_code']],
                array_merge($vehicle, [
                    'organization_id' => $organization->id,
                    'min_load_qty' => 10,
                ])
            );
            $this->command->info("✅ Vehicle seeded: {$vehicle['vehicle_name']} ({$vehicle['vehicle_code']})");
        }

        $this->command->info('✅ Total vehicle types: ' . VehicleType::count());
        $this->command->info('✅ Total vehicles: ' . Vehicle::count());
    }
}
