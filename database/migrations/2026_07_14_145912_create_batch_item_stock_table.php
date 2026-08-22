<?php
// database/migrations/2026_07_14_000003_create_batch_item_stock_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_item_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_item_id')->constrained('batch_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('reserved_quantity', 15, 3)->default(0);
            $table->timestamps();

            $table->unique(['batch_item_id', 'warehouse_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_item_stock');
    }
};