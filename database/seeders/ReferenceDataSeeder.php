<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Support\Stamped;

/**
 * Tops up the shared reference tables so each one holds at least 5 rows.
 *
 * Every insert is keyed on a natural column and skipped when that row already
 * exists, so the seeder is safe to re-run and never truncates existing data.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->countries();
        $this->itemTypes();
        $this->packCatalog();
        $this->warehouseAndVehicleTypes();
        $this->currencies();
        $this->organizations();
        $this->warehouses();
        $this->vehicles();
        $this->priceLists();
        $this->brands();
        $this->categories();
        $this->packageStatuses();
        $this->salesOrderTypes();
        $this->paymentDeadlineSettings();
    }

    private function countries(): void
    {
        $countries = [
            ['name' => 'ليبيا', 'code' => 'LY'],
            ['name' => 'تونس', 'code' => 'TN'],
            ['name' => 'مصر', 'code' => 'EG'],
            ['name' => 'السودان', 'code' => 'SD'],
            ['name' => 'الجزائر', 'code' => 'DZ'],
            ['name' => 'المغرب', 'code' => 'MA'],
        ];

        foreach ($countries as $country) {
            Stamped::upsert('countries', ['code' => $country['code']],  $country);
        }

        // Some city/state/area tables key off country; make sure the lookups
        // used by the address pickers have enough options.
        $libya = DB::table('countries')->where('code', 'LY')->first();
        if (! $libya) {
            return;
        }

        $states = [
            'طرابلس' => 'TB',
            'بنغازي' => 'BG',
            'مصراتة' => 'MT',
            'الزاوية' => 'ZW',
            'سبها' => 'SH',
        ];

        foreach ($states as $name => $code) {
            Stamped::upsert('states', 
                ['country_id' => $libya->id, 'name_ar' => $name], 
                ['country_id' => $libya->id, 'name_ar' => $name, 'name_en' => $name, 'code' => $code, 'is_active' => true, 'sort_order' => 1]);
        }

        $cities = [
            ['state_id' => $this->stateId('طرابلس'), 'name' => 'طرابلس المركز', 'code' => 'TBC'],
            ['state_id' => $this->stateId('طرابلس'), 'name' => 'قرقارش', 'code' => 'QRK'],
            ['state_id' => $this->stateId('بنغازي'), 'name' => 'بنغازي المركز', 'code' => 'BGC'],
            ['state_id' => $this->stateId('مصراتة'), 'name' => 'مصراتة المركز', 'code' => 'MTC'],
            ['state_id' => $this->stateId('الزاوية'), 'name' => 'الزاوية المركز', 'code' => 'ZWC'],
            ['state_id' => $this->stateId('سبها'), 'name' => 'سبها المركز', 'code' => 'SHC'],
        ];

        foreach ($cities as $city) {
            if (! $city['state_id']) {
                continue;
            }

            // The unique key is (country_id, name), not (state_id, name).
            Stamped::upsert(
                'cities',
                ['country_id' => $libya->id, 'name' => $city['name']],
                $city + [
                    'country_id' => $libya->id,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
            );
        }
    }

    private function stateId(string $name): ?int
    {
        return DB::table('states')->where('name_ar', $name)->value('id');
    }

    private function itemTypes(): void
    {
        $types = [
            ['name' => 'مواد غذائية', 'code' => 'FOOD', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'مشروبات', 'code' => 'DRINK', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'منظفات', 'code' => 'CLEAN', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'ألبان', 'code' => 'DAIRY', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'معلبات', 'code' => 'CANNED', 'is_active' => true, 'sort_order' => 5],
            ['name' => 'حلويات', 'code' => 'SWEET', 'is_active' => true, 'sort_order' => 6],
        ];

        foreach ($types as $type) {
            Stamped::upsert('item_types', ['code' => $type['code']],  $type);
        }
    }

    private function packCatalog(): void
    {
        $packTypes = [
            ['name' => 'قطعة', 'name_ar' => 'قطعة', 'name_en' => 'Piece', 'code' => 'PCS', 'pieces_per_pack' => 1],
            ['name' => 'كرتون', 'name_ar' => 'كرتون', 'name_en' => 'Carton', 'code' => 'CTN', 'pieces_per_pack' => 12],
            ['name' => 'باكيت', 'name_ar' => 'باكيت', 'name_en' => 'Packet', 'code' => 'PKT', 'pieces_per_pack' => 6],
            ['name' => 'شريط', 'name_ar' => 'شريط', 'name_en' => 'Strip', 'code' => 'SHT', 'pieces_per_pack' => 10],
            ['name' => 'ثيلة', 'name_ar' => 'ثيلة', 'name_en' => 'Thimble', 'code' => 'THL', 'pieces_per_pack' => 50],
        ];

        foreach ($packTypes as $row) {
            Stamped::upsert('pack_types', ['code' => $row['code']],  $row);
        }

        $packGroups = [
            ['name_ar' => 'مجموعة تجزئة', 'name_en' => 'Retail Group', 'code' => 'RETAIL', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'مجموعة جملة', 'name_en' => 'Wholesale Group', 'code' => 'WHOLESALE', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'مجموعة مطاعم', 'name_en' => 'Restaurant Group', 'code' => 'RESTAURANT', 'is_active' => true, 'sort_order' => 3],
            ['name_ar' => 'مجموعة خاصة', 'name_en' => 'Special Group', 'code' => 'SPECIAL', 'is_active' => true, 'sort_order' => 4],
            ['name_ar' => 'مجموعة موسمية', 'name_en' => 'Seasonal Group', 'code' => 'SEASONAL', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($packGroups as $row) {
            Stamped::upsert('pack_groups', ['code' => $row['code']],  $row);
        }

        $packOperations = [
            ['name_ar' => 'بيع', 'name_en' => 'Sale', 'code' => 'SALE', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'شراء', 'name_en' => 'Purchase', 'code' => 'PURCHASE', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'إرجاع', 'name_en' => 'Return', 'code' => 'RETURN', 'is_active' => true, 'sort_order' => 3],
            ['name_ar' => 'تحويل', 'name_en' => 'Transfer', 'code' => 'TRANSFER', 'is_active' => true, 'sort_order' => 4],
            ['name_ar' => 'جرد', 'name_en' => 'Stocktake', 'code' => 'STOCKTAKE', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($packOperations as $row) {
            Stamped::upsert('pack_operations', ['code' => $row['code']],  $row);
        }

        $uom = DB::table('uoms')->value('id');

        $capacities = [
            ['value' => 1, 'name_ar' => 'قطعة واحدة', 'name_en' => 'One piece'],
            ['value' => 6, 'name_ar' => 'ست قطع', 'name_en' => 'Six pieces'],
            ['value' => 12, 'name_ar' => 'اثنتا عشرة قطعة', 'name_en' => 'Twelve pieces'],
            ['value' => 24, 'name_ar' => 'أربعة وعشرون', 'name_en' => 'Twenty four'],
            ['value' => 50, 'name_ar' => 'خمسون قطعة', 'name_en' => 'Fifty pieces'],
        ];

        if ($uom === null) {
            return;
        }

        foreach ($capacities as $row) {
            Stamped::upsert('pack_capacities', 
                ['value' => $row['value'], 'uom_id' => $uom], 
                $row + ['uom_id' => $uom]);
        }
    }

    private function warehouseAndVehicleTypes(): void
    {
        $warehouseTypes = [
            ['name_ar' => 'مخزن رئيسي', 'name_en' => 'Main Warehouse', 'code' => 'MAIN', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'مخزن فرعي', 'name_en' => 'Branch Warehouse', 'code' => 'BRANCH', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'مخزن المركبات', 'name_en' => 'Van Warehouse', 'code' => 'VAN', 'is_active' => true, 'sort_order' => 3],
            ['name_ar' => 'مخزن بارد', 'name_en' => 'Cold Warehouse', 'code' => 'COLD', 'is_active' => true, 'sort_order' => 4],
            ['name_ar' => 'مخزن مرتجعات', 'name_en' => 'Returns Warehouse', 'code' => 'RETURN', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($warehouseTypes as $row) {
            Stamped::upsert('warehouse_types', ['code' => $row['code']],  $row);
        }

        $vehicleTypes = [
            ['name_ar' => 'شاحنة صغيرة', 'name_en' => 'Light Truck', 'code' => 'LIGHT', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'شاحنة متوسطة', 'name_en' => 'Medium Truck', 'code' => 'MEDIUM', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'شاحنة كبيرة', 'name_en' => 'Heavy Truck', 'code' => 'HEAVY', 'is_active' => true, 'sort_order' => 3],
            ['name_ar' => 'فان توصيل', 'name_en' => 'Delivery Van', 'code' => 'VAN', 'is_active' => true, 'sort_order' => 4],
            ['name_ar' => 'سيارة مبردة', 'name_en' => 'Reefer Van', 'code' => 'REEFER', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($vehicleTypes as $row) {
            Stamped::upsert('vehicle_types', ['code' => $row['code']],  $row);
        }
    }

    private function currencies(): void
    {
        $currencies = [
            ['name' => 'دينار ليبي', 'code' => 'LYD', 'coin_description' => 'دينار', 'base_currency' => true, 'exchange_rate_to_base' => 1.0],
            ['name' => 'دولار أمريكي', 'code' => 'USD', 'coin_description' => 'دولار', 'base_currency' => false, 'exchange_rate_to_base' => 0.21],
            ['name' => 'يورو', 'code' => 'EUR', 'coin_description' => 'يورو', 'base_currency' => false, 'exchange_rate_to_base' => 0.18],
            ['name' => 'جنيه مصري', 'code' => 'EGP', 'coin_description' => 'جنيه', 'base_currency' => false, 'exchange_rate_to_base' => 0.11],
            ['name' => 'دينار تونسي', 'code' => 'TND', 'coin_description' => 'دينار', 'base_currency' => false, 'exchange_rate_to_base' => 0.25],
        ];

        foreach ($currencies as $row) {
            Stamped::upsert('currencies', ['code' => $row['code']],  $row);
        }
    }

    private function organizations(): void
    {
        $organizations = [
            ['name' => 'شركة مجديت للصناعات الغذائية', 'code' => 'MAGGADIT', 'organization_type' => 'company', 'phone' => '0210000000', 'email' => 'info@maggadit.com'],
            ['name' => 'فرع طرابلس', 'code' => 'MAGGADIT-TB', 'organization_type' => 'branch', 'phone' => '0211111111', 'email' => 'tripoli@maggadit.com'],
            ['name' => 'فرع بنغازي', 'code' => 'MAGGADIT-BG', 'organization_type' => 'branch', 'phone' => '0222222222', 'email' => 'benghazi@maggadit.com'],
            ['name' => 'فرع مصراتة', 'code' => 'MAGGADIT-MS', 'organization_type' => 'branch', 'phone' => '0233333333', 'email' => 'misrata@maggadit.com'],
            ['name' => 'فرع سبها', 'code' => 'MAGGADIT-SH', 'organization_type' => 'branch', 'phone' => '0244444444', 'email' => 'sabha@maggadit.com'],
        ];

        foreach ($organizations as $organization) {
            Stamped::upsert(
                'organizations',
                ['code' => $organization['code']],
                $organization + [
                    'parent_id' => null,
                    'whatsapp' => $organization['phone'],
                    'website' => 'https://maggadit.com',
                    'address' => 'ليبيا',
                    'forward_contact_messages_to_email' => true,
                    'contact_email_recipient' => $organization['email'],
                ],
            );
        }
    }

    private function warehouses(): void
    {
        $typeId = \App\Models\WarehouseType::orderBy('id')->value('id');
        $orgId = \App\Models\Organization::orderBy('id')->value('id');
        $cityId = \App\Models\City::orderBy('id')->value('id');

        if (! $typeId || ! $orgId) {
            return;
        }

        $warehouses = [
            ['code' => 'WH-TR-01', 'name_en' => 'Main Warehouse Tripoli', 'name_ar' => 'المستودع الرئيسي طرابلس'],
            ['code' => 'WH-BN-01', 'name_en' => 'Warehouse Benghazi', 'name_ar' => 'مستودع بنغازي'],
            ['code' => 'WH-MS-01', 'name_en' => 'Warehouse Misrata', 'name_ar' => 'مستودع مصراتة'],
            ['code' => 'WH-ZW-01', 'name_en' => 'Warehouse Zawiya', 'name_ar' => 'مستودع الزاوية'],
            ['code' => 'WH-SH-01', 'name_en' => 'Warehouse Sabha', 'name_ar' => 'مستودع سبها'],
        ];

        foreach ($warehouses as $index => $warehouse) {
            Stamped::upsert(
                'warehouses',
                ['code' => $warehouse['code']],
                $warehouse + [
                    'warehouse_type_id' => $typeId,
                    'organization_id' => $orgId,
                    'phone' => '021' . str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                    'address_ar' => 'ليبيا',
                    'city_id' => $cityId,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function vehicles(): void
    {
        $typeId = \App\Models\VehicleType::orderBy('id')->value('id');
        $orgId = \App\Models\Organization::orderBy('id')->value('id');

        if (! $typeId || ! $orgId) {
            return;
        }

        $vehicles = [
            ['vehicle_code' => 'VH-001', 'vehicle_name' => 'شاحنة 1', 'plate_no' => '12-3456-1'],
            ['vehicle_code' => 'VH-002', 'vehicle_name' => 'شاحنة 2', 'plate_no' => '12-3456-2'],
            ['vehicle_code' => 'VH-003', 'vehicle_name' => 'سيارة توصيل 1', 'plate_no' => '12-3456-3'],
            ['vehicle_code' => 'VH-004', 'vehicle_name' => 'سيارة توصيل 2', 'plate_no' => '12-3456-4'],
            ['vehicle_code' => 'VH-005', 'vehicle_name' => 'شاحنة مبردة', 'plate_no' => '12-3456-5'],
        ];

        foreach ($vehicles as $index => $vehicle) {
            Stamped::upsert(
                'vehicles',
                ['vehicle_code' => $vehicle['vehicle_code']],
                $vehicle + [
                    'organization_id' => $orgId,
                    'vehicle_type_id' => $typeId,
                    'odometer' => 10000 + ($index * 5000),
                    'capacity' => 1000 + ($index * 250),
                    'volume' => 5 + $index,
                    'inactive' => false,
                ],
            );
        }
    }

    private function priceLists(): void
    {
        $currencyId = \App\Models\Currency::where('code', 'LYD')->value('id');
        $adminId = \App\Models\User::orderBy('id')->value('id');
        $lists = \App\Models\PriceList::orderBy('id')->get();

        $extra = [
            ['name' => 'قائمة المباني', 'description' => 'أسعار خاصة بالمباني', 'multiplier' => 0.75],
            ['name' => 'قائمة الفنادق', 'description' => 'أسعار خاصة بالفنادق', 'multiplier' => 0.8],
            ['name' => 'قائمة التصدير', 'description' => 'أسعار التصدير', 'multiplier' => 0.95],
        ];

        foreach ($extra as $row) {
            $priceList = \App\Models\PriceList::updateOrCreate(
                ['name' => $row['name']],
                [
                    'description' => $row['description'],
                    'status' => 'active',
                    'currency_id' => $currencyId,
                    'created_by' => $adminId,
                    'is_default' => false,
                ],
            );

            Stamped::upsert(
                'price_list_versions',
                [
                    'price_list_id' => $priceList->id,
                    'effective_date' => '2026-01-01',
                ],
                [
                    'price_list_id' => $priceList->id,
                    'effective_date' => '2026-01-01',
                    'is_active' => true,
                    'created_by' => $adminId,
                    'currency_id' => $currencyId,
                    'exchange_rate' => 1,
                ],
            );
        }
    }

    private function brands(): void
    {
        $brands = [
            ['name' => 'مجدية', 'show_in_customer_app' => true],
            ['name' => 'الواحة', 'show_in_customer_app' => true],
            ['name' => 'الشروق', 'show_in_customer_app' => true],
            ['name' => 'النخيل', 'show_in_customer_app' => true],
            ['name' => 'الوادي', 'show_in_customer_app' => true],
            ['name' => 'البحر', 'show_in_customer_app' => false],
        ];

        foreach ($brands as $row) {
            Stamped::upsert('brands', ['name' => $row['name']],  $row);
        }
    }

    private function categories(): void
    {
        $categories = [
            ['code' => 'GROCERY', 'name' => 'بقالة', 'division' => 'FOOD', 'show_in_customer_app' => true],
            ['code' => 'BEVERAGE', 'name' => 'مشروبات', 'division' => 'FOOD', 'show_in_customer_app' => true],
            ['code' => 'DAIRY', 'name' => 'ألبان وأجبان', 'division' => 'FOOD', 'show_in_customer_app' => true],
            ['code' => 'CANNED', 'name' => 'معلبات', 'division' => 'FOOD', 'show_in_customer_app' => true],
            ['code' => 'CLEANING', 'name' => 'منظفات', 'division' => 'NONFOOD', 'show_in_customer_app' => true],
            ['code' => 'SWEETS', 'name' => 'حلويات', 'division' => 'FOOD', 'show_in_customer_app' => true],
        ];

        foreach ($categories as $row) {
            Stamped::upsert('categories', ['code' => $row['code']],  $row);
        }
    }

    private function packageStatuses(): void
    {
        $orgId = DB::table('organizations')->value('id');

        $statuses = [
            ['status_name' => 'عادي', 'resellable' => true, 'expired' => false, 'default' => true, 'lpo_required' => false, 'is_related_to_cn_request' => false, 'is_price_editable' => true],
            ['status_name' => 'مكسور', 'resellable' => false, 'expired' => false, 'default' => false, 'lpo_required' => false, 'is_related_to_cn_request' => true, 'is_price_editable' => false],
            ['status_name' => 'منتهي الصلاحية', 'resellable' => false, 'expired' => true, 'default' => false, 'lpo_required' => false, 'is_related_to_cn_request' => true, 'is_price_editable' => false],
            ['status_name' => 'مرتجع', 'resellable' => false, 'expired' => false, 'default' => false, 'lpo_required' => true, 'is_related_to_cn_request' => true, 'is_price_editable' => false],
            ['status_name' => 'محجوز', 'resellable' => true, 'expired' => false, 'default' => false, 'lpo_required' => false, 'is_related_to_cn_request' => false, 'is_price_editable' => false],
        ];

        foreach ($statuses as $row) {
            if (! $orgId) {
                return;
            }
            Stamped::upsert('package_statuses', 
                ['organization_id' => $orgId, 'status_name' => $row['status_name']], 
                $row + ['organization_id' => $orgId]);
        }
    }

    private function salesOrderTypes(): void
    {
        $types = [
            ['name' => 'طلبية عادية', 'code' => 'NORMAL', 'active' => true, 'default' => true],
            ['name' => 'نقدي', 'code' => 'CASH', 'active' => true, 'default' => false],
            ['name' => 'آجل', 'code' => 'CREDIT', 'active' => true, 'default' => false],
            ['name' => 'مستعجل', 'code' => 'URGENT', 'active' => true, 'default' => false],
            ['name' => 'مؤسسي', 'code' => 'INSTITUTIONAL', 'active' => true, 'default' => false],
        ];

        foreach ($types as $row) {
            Stamped::upsert('sales_order_types', ['code' => $row['code']],  $row);
        }
    }

    private function paymentDeadlineSettings(): void
    {
        $settings = [
            ['payment_type' => 'cash', 'hours_limit' => 0, 'is_active' => false, 'applies_to' => 'all'],
            ['payment_type' => 'credit', 'hours_limit' => 72, 'is_active' => true, 'applies_to' => 'all'],
            ['payment_type' => 'credit', 'hours_limit' => 48, 'is_active' => false, 'applies_to' => 'credit_customers'],
            ['payment_type' => 'prepaid', 'hours_limit' => 24, 'is_active' => true, 'applies_to' => 'institutions'],
            ['payment_type' => 'prepaid', 'hours_limit' => 12, 'is_active' => false, 'applies_to' => 'new_customers'],
        ];

        foreach ($settings as $row) {
            Stamped::upsert('payment_deadline_settings', 
                ['payment_type' => $row['payment_type'], 'hours_limit' => $row['hours_limit'], 'applies_to' => $row['applies_to']], 
                $row);
        }
    }
}
