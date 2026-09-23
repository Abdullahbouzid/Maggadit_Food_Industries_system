<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offers') && !Schema::hasColumn('offers', 'price_list_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            });
        }

        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'price_list_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
            });
        }

        if (Schema::hasTable('invoice_items') && !Schema::hasColumn('invoice_items', 'item_pack_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'price_list_id')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropForeign(['price_list_id']);
                $table->dropColumn('price_list_id');
            });
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'price_list_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['price_list_id']);
                $table->dropColumn('price_list_id');
            });
        }

        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'item_pack_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropForeign(['item_pack_id']);
                $table->dropColumn('item_pack_id');
            });
        }
    }
};
