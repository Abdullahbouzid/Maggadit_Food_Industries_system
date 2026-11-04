<?php

namespace Database\Seeders;

use App\Models\PackCapacity;
use App\Models\PackGroup;
use App\Models\PackOperation;
use App\Models\PackType;
use App\Models\Uom;
use Illuminate\Database\Seeder;

class PackCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $packTypes = [
            ['code' => 'SINGLE', 'name' => 'Single', 'name_en' => 'Single', 'name_ar' => 'قطعة فردية', 'pieces_per_pack' => 1],
            ['code' => 'SMALL_BOX', 'name' => 'Small Box', 'name_en' => 'Small Box', 'name_ar' => 'صندوق صغير', 'pieces_per_pack' => 12],
            ['code' => 'MEDIUM_BOX', 'name' => 'Medium Box', 'name_en' => 'Medium Box', 'name_ar' => 'صندوق متوسط', 'pieces_per_pack' => 24],
            ['code' => 'LARGE_BOX', 'name' => 'Large Box', 'name_en' => 'Large Box', 'name_ar' => 'صندوق كبير', 'pieces_per_pack' => 48],
        ];

        foreach ($packTypes as $packTypeData) {
            PackType::updateOrCreate(['code' => $packTypeData['code']], $packTypeData);
            $this->command->info("✅ PackType seeded: {$packTypeData['name_ar']} ({$packTypeData['code']})");
        }

        $gUom = Uom::where('code', 'G')->first();
        $kgUom = Uom::where('code', 'KG')->first();
        $uomIdG = $gUom ? $gUom->id : null;
        $uomIdKg = $kgUom ? $kgUom->id : null;

        $packCapacities = [
            ['value' => 100, 'uom_id' => $uomIdG, 'name_en' => '100g', 'name_ar' => '100 جرام'],
            ['value' => 250, 'uom_id' => $uomIdG, 'name_en' => '250g', 'name_ar' => '250 جرام'],
            ['value' => 500, 'uom_id' => $uomIdG, 'name_en' => '500g', 'name_ar' => '500 جرام'],
            ['value' => 1, 'uom_id' => $uomIdKg, 'name_en' => '1kg', 'name_ar' => '1 كيلوجرام'],
        ];

        foreach ($packCapacities as $capData) {
            PackCapacity::updateOrCreate(
                ['value' => $capData['value'], 'uom_id' => $capData['uom_id']],
                $capData
            );
            $this->command->info("✅ PackCapacity seeded: {$capData['name_ar']}");
        }

        $packOperations = [
            ['code' => 'PACK', 'name_en' => 'Pack', 'name_ar' => 'تعبئة'],
            ['code' => 'UNPACK', 'name_en' => 'Unpack', 'name_ar' => 'فك تعبئة'],
            ['code' => 'REPACK', 'name_en' => 'Repack', 'name_ar' => 'إعادة تعبئة'],
        ];

        foreach ($packOperations as $opData) {
            PackOperation::updateOrCreate(
                ['code' => $opData['code']],
                array_merge($opData, ['is_active' => true])
            );
            $this->command->info("✅ PackOperation seeded: {$opData['name_ar']} ({$opData['code']})");
        }

        $packGroups = [
            ['code' => 'CONSUMER', 'name_en' => 'Consumer', 'name_ar' => 'للمستهلك'],
            ['code' => 'WHOLESALE', 'name_en' => 'Wholesale', 'name_ar' => 'للبيع بالجملة'],
            ['code' => 'PROMO', 'name_en' => 'Promotional', 'name_ar' => 'ترويجي'],
        ];

        foreach ($packGroups as $groupData) {
            PackGroup::updateOrCreate(
                ['code' => $groupData['code']],
                array_merge($groupData, ['is_active' => true])
            );
            $this->command->info("✅ PackGroup seeded: {$groupData['name_ar']} ({$groupData['code']})");
        }

        $this->command->info('✅ Total pack types: ' . PackType::count());
        $this->command->info('✅ Total pack capacities: ' . PackCapacity::count());
        $this->command->info('✅ Total pack operations: ' . PackOperation::count());
        $this->command->info('✅ Total pack groups: ' . PackGroup::count());
    }
}
