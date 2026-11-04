<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->string('reserved_for_type')->nullable();
            $table->unsignedBigInteger('reserved_for_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['reserved_for_type', 'reserved_for_id']);
        });

        DB::statement("ALTER TABLE stock_reservations ADD CONSTRAINT chk_stock_reservation_status CHECK (status IN ('active', 'consumed', 'released', 'expired'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
