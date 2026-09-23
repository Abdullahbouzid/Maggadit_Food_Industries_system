<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            CurrencySeeder::class,
            CountryStateCitySeeder::class,
            OrganizationSeeder::class,
            WarehouseSeeder::class,
            VehicleSeeder::class,
            ItemCatalogSeeder::class,
            PackCatalogSeeder::class,
            ItemModelSeeder::class,
            ItemSeeder::class,
            PriceListSeeder::class,
            CustomerSeeder::class,
            PaymentDeadlineSettingSeeder::class,
            CustomerWalletSeeder::class,
        ]);
    }
}
