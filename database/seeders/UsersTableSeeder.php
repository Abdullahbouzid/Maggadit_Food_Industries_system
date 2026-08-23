<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Maggadit',
            'email' => 'admin@maggadit.com',
            'password' => Hash::make('password123'),
        ]);

        // يمكنك إضافة مستخدمين إضافيين إذا أردت
        // User::create([
        //     'name' => 'User Test',
        //     'email' => 'test@example.com',
        //     'password' => Hash::make('test123'),
        // ]);
    }
}
