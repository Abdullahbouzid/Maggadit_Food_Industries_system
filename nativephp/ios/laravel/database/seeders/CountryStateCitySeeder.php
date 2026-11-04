<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

class CountryStateCitySeeder extends Seeder
{
    public function run(): void
    {
        $libya = Country::updateOrCreate(
            ['code' => 'LY'],
            [
                'name' => 'ليبيا',
                'code' => 'LY',
                'latitude' => 26.3351,
                'longitude' => 17.2283,
            ]
        );
        $this->command->info('✅ Country seeded: ليبيا (LY)');

        $states = [
            ['code' => 'TRP', 'name_en' => 'Tripoli', 'name_ar' => 'طرابلس'],
            ['code' => 'BEN', 'name_en' => 'Benghazi', 'name_ar' => 'بنغازي'],
            ['code' => 'MIS', 'name_en' => 'Misrata', 'name_ar' => 'مصراتة'],
            ['code' => 'ZAW', 'name_en' => 'Zawiya', 'name_ar' => 'الزاوية'],
            ['code' => 'SBH', 'name_en' => 'Sabha', 'name_ar' => 'سبها'],
        ];

        $stateIds = [];
        foreach ($states as $stateData) {
            $state = State::updateOrCreate(
                ['code' => $stateData['code']],
                array_merge($stateData, ['country_id' => $libya->id])
            );
            $stateIds[$stateData['code']] = $state->id;
            $this->command->info("✅ State seeded: {$stateData['name_ar']} ({$stateData['code']})");
        }

        $cities = [
            ['state_code' => 'TRP', 'name' => 'طرابلس المركز'],
            ['state_code' => 'TRP', 'name' => 'تاجوراء'],
            ['state_code' => 'TRP', 'name' => 'جنزور'],
            ['state_code' => 'BEN', 'name' => 'بنغازي المركز'],
            ['state_code' => 'BEN', 'name' => 'قمينس'],
            ['state_code' => 'BEN', 'name' => 'سلوق'],
            ['state_code' => 'MIS', 'name' => 'مصراتة المركز'],
            ['state_code' => 'MIS', 'name' => 'زليتن'],
            ['state_code' => 'MIS', 'name' => 'تورغا'],
            ['state_code' => 'ZAW', 'name' => 'الزاوية المركز'],
            ['state_code' => 'ZAW', 'name' => 'صبراتة'],
            ['state_code' => 'ZAW', 'name' => 'العجيلات'],
            ['state_code' => 'SBH', 'name' => 'سبها المركز'],
            ['state_code' => 'SBH', 'name' => 'براك الشاطئ'],
        ];

        $cityIds = [];
        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['country_id' => $libya->id, 'name' => $cityData['name']],
                [
                    'country_id' => $libya->id,
                    'state_id' => $stateIds[$cityData['state_code']],
                    'name' => $cityData['name'],
                    'is_active' => true,
                ]
            );
            $cityIds[$cityData['name']] = $city->id;
            $this->command->info("✅ City seeded: {$cityData['name']}");
        }

        $areaData = [
            ['city_name' => 'طرابلس المركز', 'code' => 'TRP_A1', 'name_en' => 'Al-Andalus', 'name_ar' => 'حي الأندلس'],
            ['city_name' => 'طرابلس المركز', 'code' => 'TRP_A2', 'name_en' => 'Al-Jumhuriya', 'name_ar' => 'شارع الجمهورية'],
            ['city_name' => 'تاجوراء', 'code' => 'TJR_A1', 'name_en' => 'Tajura Center', 'name_ar' => 'وسط تاجوراء'],
            ['city_name' => 'تاجوراء', 'code' => 'TJR_A2', 'name_en' => 'Tajura North', 'name_ar' => 'شمال تاجوراء'],
            ['city_name' => 'جنزور', 'code' => 'JNZ_A1', 'name_en' => 'Janzur Center', 'name_ar' => 'وسط جنزور'],
            ['city_name' => 'جنزور', 'code' => 'JNZ_A2', 'name_en' => 'Janzur South', 'name_ar' => 'جنوع جنزور'],
            ['city_name' => 'بنغازي المركز', 'code' => 'BEN_A1', 'name_en' => 'Benghazi Center', 'name_ar' => 'وسط بنغازي'],
            ['city_name' => 'بنغازي المركز', 'code' => 'BEN_A2', 'name_en' => 'Benghazi South', 'name_ar' => 'جنوع بنغازي'],
            ['city_name' => 'قمينس', 'code' => 'QMN_A1', 'name_en' => 'Qaminas Center', 'name_ar' => 'وسط قمينس'],
            ['city_name' => 'قمينس', 'code' => 'QMN_A2', 'name_en' => 'Qaminas North', 'name_ar' => 'شمال قمينس'],
            ['city_name' => 'سلوق', 'code' => 'SLQ_A1', 'name_en' => 'Suluq Center', 'name_ar' => 'وسط سلوق'],
            ['city_name' => 'سلوق', 'code' => 'SLQ_A2', 'name_en' => 'Suluq East', 'name_ar' => 'شرق سلوق'],
            ['city_name' => 'مصراتة المركز', 'code' => 'MIS_A1', 'name_en' => 'Misrata Center', 'name_ar' => 'وسط مصراتة'],
            ['city_name' => 'مصراتة المركز', 'code' => 'MIS_A2', 'name_en' => 'Misrata North', 'name_ar' => 'شمال مصراتة'],
            ['city_name' => 'زليتن', 'code' => 'ZLT_A1', 'name_en' => 'Zliten Center', 'name_ar' => 'وسط زليتن'],
            ['city_name' => 'زليتن', 'code' => 'ZLT_A2', 'name_en' => 'Zliten South', 'name_ar' => 'جنوع زليتن'],
            ['city_name' => 'تورغا', 'code' => 'TRG_A1', 'name_en' => 'Turga Center', 'name_ar' => 'وسط تورغا'],
            ['city_name' => 'تورغا', 'code' => 'TRG_A2', 'name_en' => 'Turga East', 'name_ar' => 'شرق تورغا'],
            ['city_name' => 'الزاوية المركز', 'code' => 'ZAW_A1', 'name_en' => 'Zawiya Center', 'name_ar' => 'وسط الزاوية'],
            ['city_name' => 'الزاوية المركز', 'code' => 'ZAW_A2', 'name_en' => 'Zawiya South', 'name_ar' => 'جنوع الزاوية'],
            ['city_name' => 'صبراتة', 'code' => 'SBR_A1', 'name_en' => 'Sabratha Center', 'name_ar' => 'وسط صبراتة'],
            ['city_name' => 'صبراتة', 'code' => 'SBR_A2', 'name_en' => 'Sabratha East', 'name_ar' => 'شرق صبراتة'],
            ['city_name' => 'العجيلات', 'code' => 'AJL_A1', 'name_en' => 'Ajaylat Center', 'name_ar' => 'وسط العجيلات'],
            ['city_name' => 'العجيلات', 'code' => 'AJL_A2', 'name_en' => 'Ajaylat South', 'name_ar' => 'جنوع العجيلات'],
            ['city_name' => 'سبها المركز', 'code' => 'SBH_A1', 'name_en' => 'Sabha Center', 'name_ar' => 'وسط سبها'],
            ['city_name' => 'سبها المركز', 'code' => 'SBH_A2', 'name_en' => 'Sabha North', 'name_ar' => 'شمال سبها'],
            ['city_name' => 'براك الشاطئ', 'code' => 'BRK_A1', 'name_en' => 'Birak Shattic', 'name_ar' => 'وسط براك الشاطئ'],
            ['city_name' => 'براك الشاطئ', 'code' => 'BRK_A2', 'name_en' => 'Birak Shattic East', 'name_ar' => 'شرق براك الشاطئ'],
        ];

        $areaIndex = 0;
        foreach ($areaData as $data) {
            $area = Area::updateOrCreate(
                ['code' => $data['code']],
                [
                    'code' => $data['code'],
                    'name_en' => $data['name_en'],
                    'name_ar' => $data['name_ar'],
                    'state_id' => null,
                    'city_id' => $cityIds[$data['city_name']],
                    'is_active' => true,
                    'sort_order' => $areaIndex++,
                ]
            );
            $this->command->info("✅ Area seeded: {$data['name_ar']}");
        }

        $this->command->info('✅ Total countries: ' . Country::count());
        $this->command->info('✅ Total states: ' . State::count());
        $this->command->info('✅ Total cities: ' . City::count());
        $this->command->info('✅ Total areas: ' . Area::count());
    }
}
