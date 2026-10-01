<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Tops up customers and the CRM tables around them (wallets, favorites,
 * price-list assignments, vendors, competitors, contact messages, devices).
 */
class CrmDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->customers();
        $this->customerOrganization();
        $this->customerPriceLists();
        $this->wallets();
        $this->favorites();
        $this->vendors();
        $this->competitors();
        $this->contactMessages();
        $this->devices();
    }

    private function customers(): void
    {
        $orgId = DB::table('organizations')->value('id');
        $defaultPriceList = DB::table('price_lists')->value('id');

        $customers = [
            ['username' => 'cust001', 'name' => 'أحمد السوق', 'type' => 'cash', 'credit_limit' => 0],
            ['username' => 'cust002', 'name' => 'سوق الأمانة', 'type' => 'credit', 'credit_limit' => 5000],
            ['username' => 'cust003', 'name' => 'بقالة الخير', 'type' => 'cash', 'credit_limit' => 0],
            ['username' => 'cust004', 'name' => 'مطعم الشرق', 'type' => 'credit', 'credit_limit' => 5000],
            ['username' => 'cust005', 'name' => 'سوبر ماركت المدينة', 'type' => 'credit', 'credit_limit' => 8000],
            ['username' => 'cust006', 'name' => 'كافيتيريا النخبة', 'type' => 'cash', 'credit_limit' => 0],
            ['username' => 'cust007', 'name' => 'مطعم البيت الدمشقي', 'type' => 'credit', 'credit_limit' => 5000],
            ['username' => 'cust008', 'name' => 'بقالة النور', 'type' => 'cash', 'credit_limit' => 0],
            ['username' => 'cust009', 'name' => 'أسواق الواحة', 'type' => 'credit', 'credit_limit' => 6000],
            ['username' => 'cust010', 'name' => 'فندق السلام', 'type' => 'credit', 'credit_limit' => 5000],
        ];

        $cities = DB::table('cities')->pluck('id', 'name');

        foreach ($customers as $index => $customer) {
            $cityName = $cities->keys()->get($index % max(1, $cities->count()));

            DB::table('customers')->updateOrInsert(
                ['username' => $customer['username']],
                [
                    'name' => $customer['name'],
                    'email' => $customer['username'].'@example.com',
                    'password' => Hash::make('password'),
                    'type' => $customer['type'],
                    'credit_limit' => $customer['credit_limit'],
                    'phone' => '09' . str_pad((string) (1000000 + $index), 7, '0', STR_PAD_LEFT),
                    'default_address' => 'شارع الجمهورية، ' . ($cityName ?? 'طرابلس'),
                    'active_organization_id' => $orgId,
                    'price_list_id' => $defaultPriceList,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function customerOrganization(): void
    {
        $orgId = DB::table('organizations')->value('id');
        $customers = DB::table('customers')->pluck('id');

        if (! $orgId) {
            return;
        }

        foreach ($customers as $customerId) {
            DB::table('customer_organization')->updateOrInsert(
                ['customer_id' => $customerId, 'organization_id' => $orgId],
                ['customer_id' => $customerId, 'organization_id' => $orgId, 'is_active' => true],
            );
        }
    }

    private function customerPriceLists(): void
    {
        $priceLists = DB::table('price_lists')->pluck('id', 'name');
        $customers = DB::table('customers')->orderBy('id')->get();

        if ($priceLists->isEmpty()) {
            return;
        }

        $names = $priceLists->keys()->values();
        $adminId = DB::table('users')->orderBy('id')->value('id');

        foreach ($customers as $customer) {
            $listId = $priceLists->get($names->get($customer->id % $names->count()));

            DB::table('customer_price_lists')->updateOrInsert(
                ['customer_id' => $customer->id, 'price_list_id' => $listId],
                [
                    'customer_id' => $customer->id,
                    'price_list_id' => $listId,
                    'assigned_at' => now(),
                    'assigned_by' => $adminId,
                    'is_active' => true,
                ],
            );
        }
    }

    private function wallets(): void
    {
        $currency = DB::table('currencies')->where('code', 'LYD')->first();
        $adminId = DB::table('users')->orderBy('id')->value('id');

        if (! $currency) {
            return;
        }

        $balances = [
            'cust001' => 2400, 'cust002' => 2000, 'cust003' => 750,
            'cust004' => 500, 'cust005' => 3000, 'cust006' => 1200,
            'cust007' => 1000, 'cust008' => 640, 'cust009' => 1500,
            'cust010' => 800,
        ];

        $customers = DB::table('customers')->orderBy('id')->get();

        foreach ($customers as $customer) {
            $balance = (float) ($balances[$customer->username] ?? 500);

            DB::table('customer_wallets')->updateOrInsert(
                ['customer_id' => $customer->id, 'currency_id' => $currency->id],
                [
                    'customer_id' => $customer->id,
                    'currency_id' => $currency->id,
                    'balance' => $balance,
                    'total_deposited' => $balance,
                    'total_spent' => 0,
                    'is_active' => true,
                ],
            );

            // Give every wallet a short movement history so the ledger view
            // has something to display.
            $walletId = DB::table('customer_wallets')
                ->where('customer_id', $customer->id)
                ->value('id');

            if (! $walletId) {
                continue;
            }

            $hasHistory = DB::table('wallet_transactions')->where('wallet_id', $walletId)->exists();
            if ($hasHistory) {
                continue;
            }

            $running = 0.0;
            $deposits = [round($balance * 0.5, 2), round($balance * 0.3, 2), round($balance * 0.2, 2)];

            foreach ($deposits as $index => $amount) {
                $running += $amount;

                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $walletId,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_after' => $running,
                    'currency_id' => $currency->id,
                    'exchange_rate' => 1,
                    'method' => 'cash',
                    'created_by' => $adminId,
                    'notes' => 'إيداع افتتاحي',
                    'created_at' => now()->subDays(30 - ($index * 7)),
                ]);
            }
        }
    }

    private function favorites(): void
    {
        $customers = DB::table('customers')->orderBy('id')->pluck('id');
        $items = DB::table('items')->orderBy('id')->pluck('id');

        if ($customers->isEmpty() || $items->isEmpty()) {
            return;
        }

        foreach ($customers as $customerId) {
            // Two favourites each, guaranteed distinct.
            $picks = [$items->get(0), $items->get(1 % $items->count())];

            foreach (array_unique($picks) as $itemId) {
                DB::table('customer_favorites')->updateOrInsert(
                    ['customer_id' => $customerId, 'item_id' => $itemId],
                    ['customer_id' => $customerId, 'item_id' => $itemId],
                );
            }
        }
    }

    private function vendors(): void
    {
        $vendors = [
            ['vendor_name' => 'شركة النور للتوريدات', 'code' => 'V001', 'phone' => '0911111111', 'commission_percentage' => 5],
            ['vendor_name' => 'مؤسسة الفجر', 'code' => 'V002', 'phone' => '0922222222', 'commission_percentage' => 7],
            ['vendor_name' => 'شركة الواحة للتجارة', 'code' => 'V003', 'phone' => '0933333333', 'commission_percentage' => 6],
            ['vendor_name' => 'مصنع المجد', 'code' => 'V004', 'phone' => '0944444444', 'commission_percentage' => 8],
            ['vendor_name' => 'شركة;DRIFT للإمداد', 'code' => 'V005', 'phone' => '0955555555', 'commission_percentage' => 4],
        ];

        foreach ($vendors as $vendor) {
            DB::table('vendors')->updateOrInsert(['code' => $vendor['code']], $vendor);
        }
    }

    private function competitors(): void
    {
        $orgId = DB::table('organizations')->value('id');

        if (! $orgId) {
            return;
        }

        $competitors = [
            ['name' => 'شركة النور التجارية', 'code' => 'C001', 'competitor_type' => 'retailer'],
            ['name' => 'مؤسسة الأمل', 'code' => 'C002', 'competitor_type' => 'wholesaler'],
            ['name' => 'سوبر ماركت الواحة', 'code' => 'C003', 'competitor_type' => 'retailer'],
            ['name' => 'مطعم الساحل', 'code' => 'C004', 'competitor_type' => 'restaurant'],
            ['name' => 'شركة الصفا', 'code' => 'C005', 'competitor_type' => 'wholesaler'],
        ];

        foreach ($competitors as $competitor) {
            DB::table('competitors')->updateOrInsert(
                ['code' => $competitor['code']],
                $competitor + [
                    'organization_id' => $orgId,
                    'contact_person' => 'مدير المبيعات',
                    'phone' => '091' . random_int(100000, 999999),
                    'email' => strtolower($competitor['code']).'@example.com',
                    'address' => 'طرابلس، ليبيا',
                ],
            );
        }
    }

    private function contactMessages(): void
    {
        $orgId = DB::table('organizations')->value('id');

        if (! $orgId) {
            return;
        }

        $messages = [
            ['subject' => 'استفسار عن الأسعار', 'message' => 'هل يمكن توفير قائمة أسعار محدثة؟', 'status' => 'pending', 'importance' => 'normal'],
            ['subject' => 'طلب عرض سعر', 'message' => 'نرغب في عرض سعر لطلبية كبيرة.', 'status' => 'pending', 'importance' => 'high'],
            ['subject' => 'مشكلة في التوصيل', 'message' => 'الطلبية تأخرت عن موعد التسليم.', 'status' => 'in_progress', 'importance' => 'high'],
            ['subject' => 'اقتراح منتج جديد', 'message' => 'نقترح إضافة منتجات جديدة إلى الكتالوج.', 'status' => 'pending', 'importance' => 'low'],
            ['subject' => 'شكوى عن الجودة', 'message' => 'هناك شكوى حول جودة أحد المنتجات.', 'status' => 'resolved', 'importance' => 'normal'],
            ['subject' => 'تحديث بيانات العميل', 'message' => 'يرجى تحديث عنوان العميل.', 'status' => 'resolved', 'importance' => 'low'],
        ];

        foreach ($messages as $index => $message) {
            DB::table('contact_messages')->insertOrIgnore([
                'organization_id' => $orgId,
                'name' => 'عميل'.($index + 1),
                'email' => 'client'.($index + 1).'@example.com',
                'phone' => '09' . str_pad((string) (1000000 + $index), 7, '0', STR_PAD_LEFT),
                'subject' => $message['subject'],
                'message' => $message['message'],
                'status' => $message['status'],
                'importance' => $message['importance'],
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ]);
        }
    }

    private function devices(): void
    {
        $orgId = DB::table('organizations')->value('id');

        if (! $orgId) {
            return;
        }

        $devices = [
            ['name_en' => 'POS Terminal 1', 'name_ar' => 'نقطة بيع 1', 'serial' => 'POS-0001', 'model_no' => 'VFD-800', 'brand' => 'SUNMI'],
            ['name_en' => 'POS Terminal 2', 'name_ar' => 'نقطة بيع 2', 'serial' => 'POS-0002', 'model_no' => 'VFD-800', 'brand' => 'SUNMI'],
            ['name_en' => 'Handheld 1', 'name_ar' => 'جهاز محمول 1', 'serial' => 'HH-0001', 'model_no' => 'CT-10', 'brand' => 'Zebra'],
            ['name_en' => 'Handheld 2', 'name_ar' => 'جهاز محمول 2', 'serial' => 'HH-0002', 'model_no' => 'CT-10', 'brand' => 'Zebra'],
            ['name_en' => 'Label Printer', 'name_ar' => 'طابعة ملصقات', 'serial' => 'PR-0001', 'model_no' => 'ZD420', 'brand' => 'Zebra'],
        ];

        foreach ($devices as $device) {
            DB::table('devices')->updateOrInsert(
                ['serial' => $device['serial']],
                $device + [
                    'organization_id' => $orgId,
                    'purchase_date' => now()->subYear()->toDateString(),
                    'warranty_expiry' => now()->addYear()->toDateString(),
                    'is_inactive' => false,
                ],
            );
        }
    }
}
