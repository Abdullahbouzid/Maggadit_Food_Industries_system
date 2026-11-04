<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            if (!Schema::hasColumn('price_list_items', 'item_pack_id')) {
                $table->foreignId('item_pack_id')->nullable()->after('item_id')
                    ->constrained('item_packs')->nullOnDelete();
            }
            if (!Schema::hasColumn('price_list_items', 'min_quantity')) {
                $table->decimal('min_quantity', 15, 3)->default(1)->after('item_pack_id');
            }
            if (!Schema::hasColumn('price_list_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->dropColumn(['min_quantity', 'discount_percent']);
            if (Schema::hasColumn('price_list_items', 'item_pack_id')) {
                $table->dropForeign(['item_pack_id']);
                $table->dropColumn('item_pack_id');
            }
        });
    }
};
