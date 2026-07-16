<?php
// database/migrations/2026_07_14_000004_create_batch_movements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batch_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_item_id')->constrained('batch_items')->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->enum('movement_type', ['in', 'transfer']);
            $table->decimal('quantity', 15, 3);
            $table->date('movement_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batch_movements');
    }
};