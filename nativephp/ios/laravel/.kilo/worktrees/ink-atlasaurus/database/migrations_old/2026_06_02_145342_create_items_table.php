<?php

// database/migrations/xxxx_xx_xx_000001_create_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('definition')->nullable();
            $table->string('image')->nullable(); // الصورة الرئيسية

            // العلاقات
            $table->foreignId('item_type_id')->nullable()->constrained('item_types')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('item_models')->nullOnDelete(); // سننشئ جدول item_models
            $table->foreignId('supplier_id')->nullable()->constrained('vendors')->nullOnDelete(); // بافتراض وجود جدول vendors

            // الحقول الرقمية
            $table->integer('shelf_life_time')->nullable();
            $table->string('sequence')->nullable();
            $table->decimal('van_stock_tolerance', 10, 2)->nullable();
            $table->decimal('storage_tolerance', 10, 2)->nullable();
            $table->decimal('weight_tolerance', 10, 2)->nullable();
            $table->decimal('low_stock_qty', 10, 2)->nullable();

            // الأعلام (flags)
            $table->boolean('is_inactive')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_price_protected')->default(false);
            $table->boolean('is_power_sku')->default(false);
            $table->boolean('is_default_batch')->default(false);
            $table->boolean('is_temp_credit')->default(false);
            $table->boolean('is_batch_priced')->default(false);
            $table->boolean('is_initiative')->default(false);
            $table->boolean('force_default_pack')->default(false);
            $table->boolean('has_serial_number')->default(false);
            $table->boolean('required_by_liter')->default(false);
            $table->boolean('required_by_weight')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};
