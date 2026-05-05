<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('uom_id')->constrained('u_o_m_s')->onDelete('restrict');
            
            // كميات المخزون
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0);
            $table->decimal('available_quantity', 15, 4)->virtualAs('quantity - reserved_quantity');
            
            // معلومات الصلاحية
            $table->string('batch_number')->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->timestamps();
            
            // unique constraint
            $table->unique(['product_id', 'warehouse_id', 'uom_id', 'batch_number'], 'inventory_unique');
            $table->index(['product_id', 'warehouse_id']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};