<?php
// database/migrations/2024_01_01_000002_add_active_organization_to_customers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('active_organization_id')->nullable()->constrained('organizations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['active_organization_id']);
            $table->dropColumn('active_organization_id');
        });
    }
};