<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Uom;
use App\Models\PackType;
use App\Models\PackCapacity;
use App\Models\PriceList;
use App\Models\PriceListVersion;
use App\Models\PriceListVersionItem;
use App\Models\Offer;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CustomerFavorite;
use App\Models\Organization;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use App\Models\Vendor;
use App\Models\ItemType;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FullDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        CustomerFavorite::truncate();
        InvoiceItem::truncate();
        Invoice::truncate();
        Offer::truncate();
        PriceListVersionItem::truncate();
        PriceListVersion::truncate();
        PriceList::truncate();
        ItemPack::truncate();
        Item::truncate();
        Brand::truncate();
        Category::truncate();
        Uom::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $uoms = [
            ['name_ar' => 'كيلو', 'name_en' => 'kg', 'uom_symbol' => 'KG', 'code' => 'KG'],
            ['name_ar' => 'لتر', 'name_en' => 'liter', 'uom_symbol' => 'L', 'code' => 'L'],
            ['name_ar' => 'قطعة', 'name_en' => 'piece', 'uom_symbol' => 'PCS', 'code' => 'PCS'],
        ];
        foreach ($uoms as $uom) {
            Uom::create($uom);
        }

        $categories = [
            ['name' => 'شوكولاتة', 'show_in_customer_app' => true, 'code' => 'CHO', 'division' => 'food'],
            ['name' => 'عسل', 'show_in_customer_app' => true, 'code' => 'HON', 'division' => 'food'],
            ['name' => 'خل', 'show_in_customer_app' => true, 'code' => 'VIN', 'division' => 'food'],
            ['name' => 'صابون', 'show_in_customer_app' => true, 'code' => 'SOA', 'division' => 'cleaning'],
        ];
        foreach ($categories as $category) {
            Category::create($category);
        }

        $brands = [
            ['name' => 'مجد', 'show_in_customer_app' => true],
            ['name' => 'طيب', 'show_in_customer_app' => true],
            ['name' => 'أصيل', 'show_in_customer_app' => true],
        ];
        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        $products = [
            ['code' => 'CHO-001', 'name_ar' => 'شوكولاتة بالحليب', 'name_en' => 'Milk Chocolate', 'category_id' => 1, 'brand_id' => 1, 'definition' => 'شوكولاتة بالحليب غنية بالطعم'],
            ['code' => 'CHO-002', 'name_ar' => 'شوكولاتة داكنة', 'name_en' => 'Dark Chocolate', 'category_id' => 1, 'brand_id' => 1, 'definition' => 'شوكولاتة داكنة 70% كاكاو'],
            ['code' => 'CHO-003', 'name_ar' => 'شوكولاتة باللوز', 'name_en' => 'Almond Chocolate', 'category_id' => 1, 'brand_id' => 2, 'definition' => 'شوكولاتة باللوز المحمص'],
            ['code' => 'HON-001', 'name_ar' => 'عسل طبيعي', 'name_en' => 'Natural Honey', 'category_id' => 2, 'brand_id' => 1, 'definition' => 'عسل طبيعي 100%'],
            ['code' => 'HON-002', 'name_ar' => 'عسل بالزعتر', 'name_en' => 'Thyme Honey', 'category_id' => 2, 'brand_id' => 3, 'definition' => 'عسل الزعتر الفاخر'],
            ['code' => 'VIN-001', 'name_ar' => 'خل تفاح', 'name_en' => 'Apple Vinegar', 'category_id' => 3, 'brand_id' => 2, 'definition' => 'خل تفاح طبيعي'],
            ['code' => 'VIN-002', 'name_ar' => 'خل عنب', 'name_en' => 'Grape Vinegar', 'category_id' => 3, 'brand_id' => 1, 'definition' => 'خل عنب أبيض'],
            ['code' => 'SOA-001', 'name_ar' => 'صابون غسيل', 'name_en' => 'Laundry Soap', 'category_id' => 4, 'brand_id' => 3, 'definition' => 'صابون غسيل فعال'],
            ['code' => 'SOA-002', 'name_ar' => 'صابون يدين', 'name_en' => 'Hand Soap', 'category_id' => 4, 'brand_id' => 2, 'definition' => 'صابون لليدين'],
        ];

        foreach ($products as $product) {
            Item::create($product);
        }

        $packTypes = [
            ['name' => 'صندوق 3*3', 'name_en' => 'Box 3×3', 'name_ar' => 'صندوق 3×3', 'code' => 'BOX_3X3', 'pieces_per_pack' => 9, 'description' => 'صندوق 3×3 يحتوي على 9 قطع'],
            ['name' => 'صندوق 4*4', 'name_en' => 'Box 4×4', 'name_ar' => 'صندوق 4×4', 'code' => 'BOX_4X4', 'pieces_per_pack' => 16, 'description' => 'صندوق 4×4 يحتوي على 16 قطعة'],
            ['name' => 'صندوق 6*6', 'name_en' => 'Box 6×6', 'name_ar' => 'صندوق 6×6', 'code' => 'BOX_6X6', 'pieces_per_pack' => 36, 'description' => 'صندوق 6×6 يحتوي على 36 قطعة'],
            ['name' => 'قطعة', 'name_en' => 'Piece', 'name_ar' => 'قطعة', 'code' => 'PIECE', 'pieces_per_pack' => 1, 'description' => 'قطعة واحدة'],
        ];
        foreach ($packTypes as $packType) {
            PackType::firstOrCreate(['code' => $packType['code']], $packType);
        }

        $gramUom = Uom::where('code', 'PCS')->first();
        $kgUom = Uom::where('code', 'KG')->first();
        $lUom = Uom::where('code', 'L')->first();

        $capacities = [];
        if ($gramUom) {
            $capacities[] = ['value' => 500, 'uom_id' => $gramUom->id, 'description' => '500 جرام'];
            $capacities[] = ['value' => 250, 'uom_id' => $gramUom->id, 'description' => '250 جرام'];
        }
        if ($kgUom) {
            $capacities[] = ['value' => 1, 'uom_id' => $kgUom->id, 'description' => '1 كيلو'];
        }
        if ($lUom) {
            $capacities[] = ['value' => 1, 'uom_id' => $lUom->id, 'description' => '1 لتر'];
            $capacities[] = ['value' => 250, 'uom_id' => $lUom->id, 'description' => '250 مللي'];
            $capacities[] = ['value' => 440, 'uom_id' => $lUom->id, 'description' => '440 مللي'];
            $capacities[] = ['value' => 500, 'uom_id' => $lUom->id, 'description' => '500 مللي'];
        }
        foreach ($capacities as $capacity) {
            PackCapacity::firstOrCreate(
                ['value' => $capacity['value'], 'uom_id' => $capacity['uom_id']],
                $capacity
            );
        }

        $packTypeIds = PackType::pluck('id', 'code');
        
        $gramUom = Uom::where('code', 'PCS')->first();
        $kgUom = Uom::where('code', 'KG')->first();
        $lUom = Uom::where('code', 'L')->first();
        
        $capacityIds = [];
        foreach (PackCapacity::all() as $capacity) {
            $capacityIds[(string) $capacity->value . '_' . $capacity->uom_id] = $capacity->id;
        }

        $packs = [
            ['item_id' => 1, 'pack_type_id' => $packTypeIds['BOX_3X3'], 'pack_capacity_id' => $capacityIds['500.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 9, 'barcode' => 'BAR001', 'is_default_pack' => true],
            ['item_id' => 1, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['250.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR002', 'is_default_pack' => false],
            ['item_id' => 2, 'pack_type_id' => $packTypeIds['BOX_3X3'], 'pack_capacity_id' => $capacityIds['500.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 9, 'barcode' => 'BAR003', 'is_default_pack' => true],
            ['item_id' => 3, 'pack_type_id' => $packTypeIds['BOX_3X3'], 'pack_capacity_id' => $capacityIds['250.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 9, 'barcode' => 'BAR004', 'is_default_pack' => true],
            ['item_id' => 4, 'pack_type_id' => $packTypeIds['BOX_4X4'], 'pack_capacity_id' => $capacityIds['500.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 16, 'barcode' => 'BAR005', 'is_default_pack' => true],
            ['item_id' => 4, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['1.00_' . ($kgUom?->id ?? 2)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR006', 'is_default_pack' => false],
            ['item_id' => 5, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['500.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR007', 'is_default_pack' => true],
            ['item_id' => 6, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['250.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR008', 'is_default_pack' => true],
            ['item_id' => 7, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['500.00_' . ($gramUom?->id ?? 4)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR009', 'is_default_pack' => true],
            ['item_id' => 8, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['1.00_' . ($kgUom?->id ?? 2)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR010', 'is_default_pack' => true],
            ['item_id' => 9, 'pack_type_id' => $packTypeIds['PIECE'], 'pack_capacity_id' => $capacityIds['1.00_' . ($kgUom?->id ?? 2)] ?? null, 'quantity_in_pack' => 1, 'barcode' => 'BAR011', 'is_default_pack' => true],
        ];

        foreach ($packs as $pack) {
            ItemPack::create($pack);
        }

        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@maggadit.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $priceList = PriceList::create([
            'name' => 'قائمة الأسعار القياسية',
            'description' => 'قائمة الأسعار الافتراضية',
            'status' => 'active',
        ]);

        $version = PriceListVersion::create([
            'price_list_id' => $priceList->id,
            'effective_date' => now()->toDateString(),
            'is_active' => true,
            'created_by' => 1,
        ]);

        $packPrices = [
            1 => 15.00, 2 => 1.25, 3 => 18.00, 4 => 22.00, 5 => 45.00,
            6 => 270.00, 7 => 55.00, 8 => 12.00, 9 => 10.00, 10 => 8.00, 11 => 5.00,
        ];

        foreach (ItemPack::all() as $pack) {
            PriceListVersionItem::create([
                'price_list_version_id' => $version->id,
                'item_id' => $pack->item_id,
                'item_pack_id' => $pack->id,
                'price' => $packPrices[$pack->id] ?? 10.00,
            ]);
        }

        $customersData = [
            ['name' => 'عميل تجريبي 1', 'username' => 'customer1_' . Str::random(4), 'email' => 'customer1_' . Str::random(4) . '@test.com', 'password' => bcrypt('password'), 'price_list_id' => $priceList->id],
            ['name' => 'عميل تجريبي 2', 'username' => 'customer2_' . Str::random(4), 'email' => 'customer2_' . Str::random(4) . '@test.com', 'password' => bcrypt('password'), 'price_list_id' => $priceList->id],
        ];
        $customers = [];
        foreach ($customersData as $customerData) {
            $customers[] = Customer::create($customerData);
        }

        $offers = [
            ['title' => 'عرض الشوكولاتة', 'description' => 'خصم 20% على جميع أنواع الشوكولاتة', 'item_id' => 1, 'item_pack_id' => 1, 'old_price' => 15.00, 'new_price' => 12.00, 'discount_percentage' => 20, 'start_date' => now(), 'end_date' => now()->addDays(30), 'is_active' => true],
            ['title' => 'عرض العسل', 'description' => 'خصم 15% على العسل الطبيعي', 'item_id' => 4, 'item_pack_id' => 5, 'old_price' => 45.00, 'new_price' => 38.25, 'discount_percentage' => 15, 'start_date' => now(), 'end_date' => now()->addDays(20), 'is_active' => true],
        ];

        foreach ($offers as $offer) {
            Offer::create($offer);
        }

        foreach ($customers as $customer) {
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'price_list_id' => $priceList->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'invoice_date' => now()->subDays(rand(1, 30)),
                'total_amount' => 0,
                'status' => 'completed',
                'notes' => 'طلب تجريبي',
            ]);

            $items = ItemPack::inRandomOrder()->take(rand(2, 4))->get();
            $total = 0;

            foreach ($items as $itemPack) {
                $qty = rand(1, 5);
                $unitPrice = match($itemPack->id) {
                    1 => 15.00, 2 => 1.25, 3 => 18.00, 4 => 22.00, 5 => 45.00,
                    6 => 270.00, 7 => 55.00, 8 => 12.00, 9 => 10.00, 10 => 8.00, 11 => 5.00,
                    default => 10.00,
                };
                $subtotal = $unitPrice * $qty;
                $total += $subtotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemPack->item_id,
                    'item_pack_id' => $itemPack->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'price_list_id' => $priceList->id,
                    'price_history_id' => $version->items()->where('item_id', $itemPack->item_id)->where('item_pack_id', $itemPack->id)->first()?->id,
                ]);
            }

            $invoice->update(['total_amount' => $total]);
        }

        foreach (Item::all() as $item) {
            foreach ($customers as $customer) {
                CustomerFavorite::create([
                    'customer_id' => $customer->id,
                    'item_id' => $item->id,
                ]);
            }
        }

        $organization = Organization::firstOrCreate(
            ['code' => 'MAG-001'],
            [
                'name' => 'مجديت للصناعات الغذائية',
                'organization_type' => 'company',
                'gln' => '5051234567890',
                'phone' => '+218 21 123 4567',
                'whatsapp' => '+218 91 234 5678',
                'email' => 'info@maggadit.com',
                'website' => 'https://www.maggadit.com',
                'address' => 'طريق الصحناوي، بنغازي، ليبيا',
                'latitude' => '32.1167',
                'longitude' => '23.9649',
                'forward_contact_messages_to_email' => true,
                'contact_email_recipient' => 'info@maggadit.com',
            ]
        );

        $country = Country::firstOrCreate(['code' => 'LY'], ['name' => 'ليبيا', 'latitude' => '26.2540', 'longitude' => '13.7120']);
        $libyaId = $country->id;

        $benghaziState = State::firstOrCreate(['code' => 'LY-BEN'], ['country_id' => $libyaId, 'name_en' => 'Benghazi', 'name_ar' => 'بنغازي', 'is_active' => true]);
        $misrataState = State::firstOrCreate(['code' => 'LY-MIS'], ['country_id' => $libyaId, 'name_en' => 'Misrata', 'name_ar' => 'مصراتة', 'is_active' => true]);
        $tripoliState = State::firstOrCreate(['code' => 'LY-TRIP'], ['country_id' => $libyaId, 'name_en' => 'Tripoli', 'name_ar' => 'طرابلس', 'is_active' => true]);

        $benghaziCity = City::firstOrCreate(['code' => 'BEN'], ['country_id' => $libyaId, 'state_id' => $benghaziState->id, 'name' => 'بنغازي']);
        $misrataCity = City::firstOrCreate(['code' => 'MIS'], ['country_id' => $libyaId, 'state_id' => $misrataState->id, 'name' => 'مصراتة']);
        $tripoliCity = City::firstOrCreate(['code' => 'TRIP'], ['country_id' => $libyaId, 'state_id' => $tripoliState->id, 'name' => 'طرابلس']);

        Area::firstOrCreate(['code' => 'BEN-SOUTH'], ['state_id' => $benghaziState->id, 'name_en' => 'Benghazi South', 'name_ar' => 'جنوب بنغازي', 'is_active' => true]);
        Area::firstOrCreate(['code' => 'BEN-NORTH'], ['state_id' => $benghaziState->id, 'name_en' => 'Benghazi North', 'name_ar' => 'شمال بنغازي', 'is_active' => true]);
        Area::firstOrCreate(['code' => 'MIS-CENTRAL'], ['state_id' => $misrataState->id, 'name_en' => 'Misrata Central', 'name_ar' => 'وسط مصراتة', 'is_active' => true]);
        Area::firstOrCreate(['code' => 'TRIP-CENTRAL'], ['state_id' => $tripoliState->id, 'name_en' => 'Tripoli Central', 'name_ar' => 'وسط طرابلس', 'is_active' => true]);

        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $customer->update(['active_organization_id' => $organization->id]);
            }
        }

        $warehouseTypes = [
            ['code' => 'MAIN', 'name_en' => 'Main Warehouse', 'name_ar' => 'المستودع الرئيسي', 'description' => 'المستودع الرئيسي للشركة'],
            ['code' => 'REG', 'name_en' => 'Regional Warehouse', 'name_ar' => 'مستودع إقليمي', 'description' => 'مستودع إقليمي فرعي'],
            ['code' => 'DRY', 'name_en' => 'Dry Storage', 'name_ar' => 'مخزن جاف', 'description' => 'مخزن للمنتجات غير المبردة'],
            ['code' => 'FRZ', 'name_en' => 'Cold Storage', 'name_ar' => 'مخزن مبرد', 'description' => 'مخزن للمنتجات المبردة'],
        ];
        foreach ($warehouseTypes as $wt) {
            WarehouseType::firstOrCreate(['code' => $wt['code']], $wt);
        }

        $warehouses = [
            ['code' => 'WH-BENZ', 'name_en' => 'Benghazi Main', 'name_ar' => 'مطارد بنغازي الرئيسي', 'warehouse_type_id' => 1, 'organization_id' => $organization->id,
              'phone' => '+218 62 234 5678', 'address_ar' => 'شارع الملك الأمير أحمد بن لبيب، بنغازي، ليبيا', 'address_en' => 'Princess Ahmed Ben Lebaid St, Benghazi, Libya',
              'country_id' => $libyaId, 'state_id' => $benghaziState->id, 'city_id' => $benghaziCity->id, 'is_active' => true],
            ['code' => 'WH-MIS', 'name_en' => 'Misrata Warehouse', 'name_ar' => 'مستودع مصراتة', 'warehouse_type_id' => 2, 'organization_id' => $organization->id,
              'phone' => '+218 62 345 6789', 'address_ar' => 'طريق المصراة الصناعية، مصراتة، ليبيا', 'address_en' => 'Industrial Road, Misrata, Libya',
              'country_id' => $libyaId, 'state_id' => $misrataState->id, 'city_id' => $misrataCity->id, 'is_active' => true],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::firstOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
        }

        foreach (ItemPack::all() as $index => $pack) {
            $whId = $index < 6 ? 1 : 2;
            $pack->update(['warehouse_id' => $whId]);
        }

        DB::table('item_types')->insertOrIgnore([
            ['code' => 'FOOD', 'name' => 'غذائي', 'description' => 'منتجات غذائية', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CLEAN', 'name' => 'تنظيف', 'description' => 'منتجات تنظيف', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (Item::all() as $item) {
            if ($item->item_type_id) continue;
            $type = $item->is_new ? 'FOOD' : 'FOOD';
            $item->update(['item_type_id' => \App\Models\ItemType::where('code', $type)->first()?->id]);
        }

        DB::table('item_pack_pack_group')->insertOrIgnore([
            ['item_pack_id' => 1, 'pack_group_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['item_pack_id' => 4, 'pack_group_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['item_pack_id' => 5, 'pack_group_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('item_pack_pack_operation')->insertOrIgnore([
            ['item_pack_id' => 1, 'pack_operation_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['item_pack_id' => 1, 'pack_operation_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['item_pack_id' => 2, 'pack_operation_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $vendors = [
            ['vendor_name' => 'شركة الغذاءات الأصيلة', 'code' => 'VEND001', 'phone' => '+218 21 234 5678', 'contact_person' => 'محمد علي', 'contact_number' => '+218 91 234 5678', 'street' => 'طريق الصحناوي، بنغازي'],
            ['vendor_name' => 'مصنع العسل الطبيعي', 'code' => 'VEND002', 'phone' => '+218 21 345 6789', 'contact_person' => 'عبدالرحمن أحمد', 'contact_number' => '+218 92 345 6789', 'street' => 'شارع الجامعة، مصراتة'],
        ];
        foreach ($vendors as $vendor) {
            \App\Models\Vendor::firstOrCreate(['code' => $vendor['code']], $vendor);
        }

        $banners = [
            ['title' => 'عرض الخاص بالشوكولاتة', 'image' => 'banners/chocolate.jpg', 'type' => 'product', 'url' => '#', 'show_in_customer_app' => true, 'order' => 1],
            ['title' => 'عرض العسل الطبيعي', 'image' => 'banners/honey.jpg', 'type' => 'product', 'url' => '#', 'show_in_customer_app' => true, 'order' => 2],
        ];
        foreach ($banners as $banner) {
            Banner::firstOrCreate(['title' => $banner['title']], $banner);
        }

        $faqs = [
            ['category' => 'general', 'question' => 'كيف أطلب منتجات؟', 'answer' => 'يمكنك طلب المنتجات عبر تطبيق الموبايل أو موقعنا الإلكتروني.'],
            ['category' => 'general', 'question' => 'ما ساعات التوصيل؟', 'answer' => 'نقوم بالتوصيل من الأحد إلى الخميس، من الساعة 8 صباحاً حتى 10 مساءً.'],
            ['category' => 'returns', 'question' => 'هل يمكن إرجاع المنتجات؟', 'answer' => 'نقبل الإرجاع خلال 14 يوماً من تاريخ الشراء بشرط أن تكون المنتجات في حالتها الأصلية.'],
        ];
        foreach ($faqs as $faq) {
            Faq::firstOrCreate(['question' => $faq['question']], $faq);
        }

        DB::table('currencies')->insertOrIgnore([
            ['name' => 'ليبي دينار', 'code' => 'LYD', 'coin_description' => 'ليبي دينار', 'base_currency' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'US Dollar', 'code' => 'USD', 'coin_description' => 'US Dollar', 'base_currency' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Euro', 'code' => 'EUR', 'coin_description' => 'Euro', 'base_currency' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('package_statuses')->insertOrIgnore([
            ['status_name' => 'معلق', 'organization_id' => $organization->id, 'default' => true, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'قيد الشحن', 'organization_id' => $organization->id, 'default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'توصيل', 'organization_id' => $organization->id, 'default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'ملغي', 'organization_id' => $organization->id, 'default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pack_operations')->insertOrIgnore([
            ['code' => 'PICK', 'name_en' => 'Pick', 'name_ar' => 'اختيار', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PACK', 'name_en' => 'Pack', 'name_ar' => 'تعبئة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SHIP', 'name_en' => 'Ship', 'name_ar' => 'شحن', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pack_groups')->insertOrIgnore([
            ['code' => 'FRAGILE', 'name_en' => 'Fragile', 'name_ar' => 'هش', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'HAZMAT', 'name_en' => 'Hazardous', 'name_ar' => 'خطر', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'FRESH', 'name_en' => 'Fresh', 'name_ar' => 'طازج', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('item_models')->insertOrIgnore([
            ['code' => 'MOD-001', 'name_en' => 'Standard Box', 'name_ar' => 'صندوق قياسي', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'MOD-002', 'name_en' => 'Glass Bottle', 'name_ar' => 'زجاجة زجاجية', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('sales_order_types')->insertOrIgnore([
            ['name' => 'طلب بيع', 'code' => 'SALE', 'active' => true, 'default' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'طلب شراء', 'code' => 'PURCHASE', 'active' => true, 'default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('vehicle_types')->insertOrIgnore([
            ['code' => 'TRUCK', 'name_en' => 'Truck', 'name_ar' => 'شاحنة', 'description' => 'شاحنة توصيل', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'VAN', 'name_en' => 'Van', 'name_ar' => 'فان', 'description' => 'مركبة صغيرة', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BIKE', 'name_en' => 'Motorcycle', 'name_ar' => 'دراجة نارية', 'description' => 'توصيل سريع', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('competitors')->insertOrIgnore([
            ['name' => 'شركة المنافس الأولى', 'code' => 'COMP001', 'organization_id' => $organization->id, 'competitor_type' => 'direct', 'contact_person' => 'عمر أحمد', 'phone' => '+218 91 111 2222', 'email' => 'info@competitor.com', 'address' => 'طرابلس، ليبيا', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شركة المنافس الثانية', 'code' => 'COMP002', 'organization_id' => $organization->id, 'competitor_type' => 'indirect', 'contact_person' => 'خالد محمود', 'phone' => '+218 92 333 4444', 'email' => 'contact@competitor2.com', 'address' => 'مصراتة، ليبيا', 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (Item::all() as $item) {
            DB::table('item_images')->insertOrIgnore([
                'item_id' => $item->id,
                'image_path' => 'items/default-' . $item->id . '.jpg',
                'alt_text' => $item->name_ar,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('organization_abouts')->insertOrIgnore([
            'organization_id' => $organization->id,
            'title' => 'مجديت للصناعات الغذائية',
            'description' => 'شركة رائدة في إنتاج المنتجات الغذائية العالية الجودة',
            'vision' => 'أن نكون الشركة الرائدة في الصناعات الغذائية بالمنطقة',
            'mission' => 'توفير منتجات غذائية صحية وآمنة للجميع',
            'values' => 'الجودة، السلامة، الابتكار، المسؤولية',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
