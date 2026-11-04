<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('batch_item_id')->nullable()->constrained('batch_items')->nullOnDelete();
            $table->string('type');
            $table->decimal('quantity', 15, 3);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 15, 3)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        DB::statement("ALTER TABLE inventory_movements ADD CONSTRAINT chk_inventory_movement_type CHECK (type IN ('in', 'out', 'reserve', 'release', 'adjustment', 'damage', 'return_good', 'return_damaged'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
