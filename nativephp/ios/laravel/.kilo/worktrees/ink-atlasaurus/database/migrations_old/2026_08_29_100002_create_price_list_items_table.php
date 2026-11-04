<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the price_list_items table.
 * 
 * This table stores direct price list items (not versioned) for simple pricing.
 * Each row links a price_list to an item (and optionally a pack) with a price,
 * quantity tier (min_quantity), discount, and validity period.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_list_items')) {
            Schema::create('price_list_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->decimal('price', 10, 2);
                $table->decimal('min_quantity', 15, 3)->default(1);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();

                $table->index(['price_list_id', 'item_id', 'item_pack_id', 'min_quantity'], 'pli_composite_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};

