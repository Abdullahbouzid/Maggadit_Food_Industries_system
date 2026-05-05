<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('technical_specs')->nullable();
            $table->string('barcode')->nullable();
            
            // العلاقات
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->constrained()->onDelete('restrict');
            $table->foreignId('default_uom_id')->constrained('u_o_m_s')->onDelete('restrict');
            
            // معلومات إضافية
            $table->decimal('weight', 10, 3)->nullable(); // الوزن بالكيلوجرام
            $table->decimal('length', 10, 3)->nullable(); // الطول بالسنتيمتر
            $table->decimal('width', 10, 3)->nullable();  // العرض بالسنتيمتر
            $table->decimal('height', 10, 3)->nullable(); // الارتفاع بالسنتيمتر
            
            // الحالة
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available_for_order')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // indexes
            $table->index('sku');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};