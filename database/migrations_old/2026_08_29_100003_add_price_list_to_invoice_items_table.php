<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'price_list_id')) {
                $table->foreignId('price_list_id')->nullable()->after('subtotal')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('invoice_items', 'price_history_id')) {
                $table->foreignId('price_history_id')->nullable()->after('price_list_id')->constrained('price_list_version_items')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['price_history_id']);
            $table->dropForeign(['price_list_id']);
            $table->dropColumn(['price_list_id', 'price_history_id']);
        });
    }
};
