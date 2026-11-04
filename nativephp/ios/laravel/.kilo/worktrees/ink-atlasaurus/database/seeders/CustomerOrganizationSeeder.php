<?php

// database/seeders/CustomerOrganizationSeeder.php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class CustomerOrganizationSeeder extends Seeder
{
    public function run()
    {
        // إنشاء منظمات تجريبية
        $org1 = Organization::create([
            'name' => 'شركة ماجديت للأغذية',
            'code' => 'MGD001',
            'organization_type' => 'شركة',
        ]);

        $org2 = Organization::create([
            'name' => 'مصنع ماجديت - فرع جدة',
            'code' => 'MGD002',
            'parent_id' => $org1->id,
            'organization_type' => 'مصنع',
        ]);

        $org3 = Organization::create([
            'name' => 'مستودع ماجديت - الرياض',
            'code' => 'MGD003',
            'parent_id' => $org1->id,
            'organization_type' => 'مستودع',
        ]);

        // إنشاء عميل تجريبي
        $customer = Customer::create([
            'name' => 'أحمد محمد',
            'username' => 'ahmed',
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password123'),
        ]);

        // ربط العميل بالمنظمات
        $customer->organizations()->attach($org1->id, ['is_active' => true]);
        $customer->organizations()->attach($org2->id, ['is_active' => false]);
        $customer->organizations()->attach($org3->id, ['is_active' => false]);

        // تعيين المنظمة النشطة
        $customer->active_organization_id = $org1->id;
        $customer->save();
    }
}
