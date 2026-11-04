<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'مدير النظام',
                'email'    => 'admin@maggadit.com',
                'password' => 'password',
                'role'     => 'admin',
            ],
            [
                'name'     => 'مدير المبيعات',
                'email'    => 'manager@maggadit.com',
                'password' => 'password',
                'role'     => 'manager',
            ],
            [
                'name'     => 'المحاسب',
                'email'    => 'accountant@maggadit.com',
                'password' => 'password',
                'role'     => 'accountant',
            ],
            [
                'name'     => 'سائق 1',
                'email'    => 'driver1@maggadit.com',
                'password' => 'password',
                'role'     => 'driver',
            ],
            [
                'name'     => 'أمين مستودع',
                'email'    => 'keeper@maggadit.com',
                'password' => 'password',
                'role'     => 'warehouse_keeper',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make($userData['password']),
                ])
            );

            $role = Role::findByName($roleName);
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            $this->command->info("✅ User created: {$user->name} ({$user->email}) → role: {$roleName}");
        }

        $this->command->info('✅ Total users: ' . User::count());
    }
}
