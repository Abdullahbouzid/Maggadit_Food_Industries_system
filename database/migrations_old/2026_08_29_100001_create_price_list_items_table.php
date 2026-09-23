<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['price_list_id', 'effective_date']);
        });

        Schema::create('price_list_version_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['price_list_version_id', 'item_id', 'item_pack_id'], 'plv_items_version_item_pack_uniq');
            $table->index(['item_id', 'item_pack_id'], 'plv_items_item_pack_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_version_items');
        Schema::dropIfExists('price_list_versions');
    }
};
