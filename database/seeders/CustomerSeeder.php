<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerPriceList;
use App\Models\Organization;
use App\Models\PriceList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('code', 'MAGGADIT')->first();
        if (!$org) {
            $this->command->error('❌ Organization not found. Run OrganizationSeeder first.');
            return;
        }

        $priceListMap = PriceList::pluck('id', 'name')->toArray();

        $customers = [
            ['username' => 'cust001', 'name' => 'أحمد السوق', 'customer_type' => 'cash', 'price_list' => 'قائمة التجزئة', 'city' => 'طرابلس المركز'],
            ['username' => 'cust002', 'name' => 'سوق الأمانة', 'customer_type' => 'credit', 'price_list' => 'قائمة الجملة', 'city' => 'طرابلس المركز'],
            ['username' => 'cust003', 'name' => 'بقالة الخير', 'customer_type' => 'cash', 'price_list' => 'قائمة التجزئة', 'city' => 'بنغازي المركز'],
            ['username' => 'cust004', 'name' => 'مطعم الشرق', 'customer_type' => 'credit', 'price_list' => 'قائمة المطاعم', 'city' => 'بنغازي المركز'],
            ['username' => 'cust005', 'name' => 'سوبر ماركت المدينة', 'customer_type' => 'credit', 'price_list' => 'قائمة الجملة', 'city' => 'مصراتة المركز'],
            ['username' => 'cust006', 'name' => 'كافيتيريا النخبة', 'customer_type' => 'cash', 'price_list' => 'قائمة التجزئة', 'city' => 'طرابلس المركز'],
            ['username' => 'cust007', 'name' => 'مطعم البيت الدمشقي', 'customer_type' => 'credit', 'price_list' => 'قائمة المطاعم', 'city' => 'طرابلس المركز'],
            ['username' => 'cust008', 'name' => 'بقالة النور', 'customer_type' => 'cash', 'price_list' => 'قائمة التجزئة', 'city' => 'الزاوية المركز'],
            ['username' => 'cust009', 'name' => 'أسواق الواحة', 'customer_type' => 'credit', 'price_list' => 'قائمة الجملة', 'city' => 'سبها المركز'],
            ['username' => 'cust010', 'name' => 'فندق السلام', 'customer_type' => 'credit', 'price_list' => 'قائمة المطاعم', 'city' => 'طرابلس المركز'],
        ];

        $cities = City::pluck('id', 'name')->toArray();

        // assigned_by is a FK to users, so it must be a real user id rather
        // than a hardcoded 1.
        $assignerId = \App\Models\User::orderBy('id')->value('id');

        foreach ($customers as $custData) {
            $creditLimit = ($custData['customer_type'] === 'credit') ? 5000.00 : 0;

            $customer = Customer::updateOrCreate(
                ['username' => $custData['username']],
                [
                    'name' => $custData['name'],
                    'username' => $custData['username'],
                    'email' => $custData['username'] . '@example.com',
                    'password' => Hash::make('password'),
                    'active_organization_id' => $org->id,
                    'price_list_id' => $priceListMap[$custData['price_list']] ?? null,
                    'type' => $custData['customer_type'],
                    'credit_limit' => $creditLimit,
                ]
            );

            CustomerPriceList::updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'customer_id' => $customer->id,
                    'price_list_id' => $customer->price_list_id,
                    'assigned_at' => now(),
                    'assigned_by' => $assignerId,
                    'is_active' => true,
                ]
            );

            $customer->organizations()->syncWithoutDetaching([
                $org->id => ['is_active' => true],
            ]);

            $this->command->info("✅ Customer seeded: {$customer->name} ({$customer->username}) → type: {$custData['customer_type']}, credit_limit: {$creditLimit}");
        }

        $this->command->info('✅ Total customers: ' . Customer::count());
        $this->command->info('✅ Total customer price lists: ' . CustomerPriceList::count());
    }
}
