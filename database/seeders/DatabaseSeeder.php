<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Foundation: roles, users, currencies, geography, org, storage.
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

            // Tops every remaining table up to at least 5 rows. All of these
            // are idempotent (keyed upserts), so re-running is safe.
            ReferenceDataSeeder::class,
            CrmDataSeeder::class,
            ContentDataSeeder::class,
            InventoryDataSeeder::class,
            SalesLifecycleSeeder::class,
            OpsDataSeeder::class,
        ]);
    }
}
