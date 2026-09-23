<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::updateOrCreate(
            ['code' => 'MAGGADIT'],
            [
                'name' => 'شركة مجديت للصناعات الغذائية',
                'code' => 'MAGGADIT',
                'organization_type' => 'head_office',
                'gln' => '6240000000001',
                'phone' => '+218911234567',
                'whatsapp' => '+218911234567',
                'email' => 'info@maggadit.com',
                'website' => 'https://maggadit.com',
                'address' => 'طرابلس، ليبيا',
                'latitude' => 32.8872,
                'longitude' => 13.1913,
                'forward_contact_messages_to_email' => true,
                'contact_email_recipient' => 'contact@maggadit.com',
            ]
        );

        $this->command->info('✅ Organization seeded: ' . $org->name . ' (' . $org->code . ')');
        $this->command->info('✅ Total organizations: ' . Organization::count());
    }
}
