<?php

namespace Database\Seeders;

use App\Models\PaymentDeadlineSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentDeadlineSettingSeeder extends Seeder
{
    public function run(): void
    {
        // User ids are not guaranteed to start at 1 after a re-seed.
        $admin = User::orderBy('id')->first();
        $updatedBy = $admin ? $admin->id : 1;

        $settings = [
            [
                'payment_type' => 'cash',
                'hours_limit' => 48,
                'is_active' => true,
                'applies_to' => 'all',
                'customer_ids' => null,
                'notes' => 'مهلة الدفع النقدي: 48 ساعة',
                'updated_by' => $updatedBy,
            ],
            [
                'payment_type' => 'credit',
                'hours_limit' => 720,
                'is_active' => true,
                'applies_to' => 'all',
                'customer_ids' => null,
                'notes' => 'مهلة الآجل: 30 يوم = 720 ساعة',
                'updated_by' => $updatedBy,
            ],
        ];

        foreach ($settings as $setting) {
            PaymentDeadlineSetting::updateOrCreate(
                ['payment_type' => $setting['payment_type']],
                $setting
            );
            $this->command->info("✅ PaymentDeadlineSetting seeded: {$setting['payment_type']} ({$setting['hours_limit']}h)");
        }

        $this->command->info('✅ Total payment deadline settings: ' . PaymentDeadlineSetting::count());
    }
}
