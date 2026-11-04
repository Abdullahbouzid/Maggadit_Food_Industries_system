<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds price column to item_packs table.
 * Used by Item::getPriceAttribute() accessor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_packs', function (Blueprint $table) {
            if (!Schema::hasColumn('item_packs', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('quantity_in_pack');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_packs', function (Blueprint $table) {
            if (Schema::hasColumn('item_packs', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
