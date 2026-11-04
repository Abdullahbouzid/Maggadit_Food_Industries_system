<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add currency_id and exchange_rate to price_list_version_items
        Schema::table('price_list_version_items', function (Blueprint $table) {
            if (!Schema::hasColumn('price_list_version_items', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('price')
                    ->constrained('currencies')->nullOnDelete();
            }
        });

        Schema::table('price_list_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('price_list_versions', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('price_list_id')
                    ->constrained('currencies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('price_list_version_items', function (Blueprint $table) {
            if (Schema::hasColumn('price_list_version_items', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });

        Schema::table('price_list_versions', function (Blueprint $table) {
            if (Schema::hasColumn('price_list_versions', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
};
