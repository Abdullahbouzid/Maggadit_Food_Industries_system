<?php
// database/migrations/2026_07_14_145845_create_batch_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->decimal('dispatched_quantity', 15, 3)->default(0);
            $table->decimal('transferred_quantity', 15, 3)->default(0);
            $table->decimal('remaining_quantity', 15, 3)->nullable();
            
            // قيد فريد لمنع تكرار نفس المنتج بنفس العبوة في الدفعة
            $table->unique(['batch_id', 'item_id', 'item_pack_id'], 'batch_items_batch_item_pack_unique');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_items');
    }
};