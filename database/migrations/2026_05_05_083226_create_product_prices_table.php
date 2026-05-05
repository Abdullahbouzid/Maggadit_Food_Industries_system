<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('price_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('uom_id')->constrained('u_o_m_s')->onDelete('restrict');
            
            $table->decimal('price', 15, 4);
            $table->decimal('min_quantity', 15, 4)->default(1);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            
            $table->timestamps();
            
            $table->unique(['product_id', 'price_list_id', 'uom_id', 'min_quantity'], 'price_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};