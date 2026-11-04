<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('item_models')) {
            Schema::create('item_models', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name_en');
                $table->string('name_ar');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name_en');
                $table->string('name_ar');
                $table->text('definition')->nullable();
                $table->string('image')->nullable();
                $table->foreignId('item_type_id')->nullable()->constrained('item_types')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->foreignId('model_id')->nullable()->constrained('item_models')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->integer('shelf_life_time')->nullable();
                $table->string('sequence')->nullable();
                $table->decimal('van_stock_tolerance', 10, 2)->nullable();
                $table->decimal('storage_tolerance', 10, 2)->nullable();
                $table->decimal('weight_tolerance', 10, 2)->nullable();
                $table->decimal('low_stock_qty', 10, 2)->nullable();
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
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('item_type_id');
                $table->index('category_id');
                $table->index('brand_id');
                $table->index('model_id');
                $table->index('supplier_id');
                $table->index('is_inactive');
                $table->index(['item_type_id', 'is_inactive']);
            });
        }

        if (!Schema::hasTable('item_packs')) {
            Schema::create('item_packs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('pack_type_id')->nullable()->constrained('pack_types')->nullOnDelete();
                $table->foreignId('pack_capacity_id')->nullable()->constrained('pack_capacities')->nullOnDelete();
                $table->integer('quantity_in_pack')->default(1);
                $table->decimal('price', 10, 2)->default(0);
                $table->string('barcode', 255)->nullable()->unique();
                $table->string('serial_separator', 255)->nullable();
                $table->decimal('equivalency_factor', 12, 4)->nullable();
                $table->decimal('height', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('depth', 10, 2)->nullable();
                $table->decimal('weight', 10, 2)->nullable();
                $table->boolean('is_weight_priced')->default(false);
                $table->boolean('allow_fraction_in_qty')->default(false);
                $table->boolean('is_default_pack')->default(false);
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                $table->index('item_id');
                $table->index('warehouse_id');
                $table->index('pack_type_id');
                $table->index('pack_capacity_id');
                $table->index('is_default_pack');
            });
        }

        if (!Schema::hasTable('item_images')) {
            Schema::create('item_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->string('image_path');
                $table->string('alt_text', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('item_id');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255)->nullable();
                $table->string('username', 255)->unique();
                $table->string('email', 255)->nullable()->unique();
                $table->string('avatar', 255)->nullable();
                $table->string('password');
                $table->text('fcm_token')->nullable();
                $table->rememberToken();
                $table->foreignId('active_organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->enum('type', ['cash', 'credit'])->default('cash');
                $table->decimal('credit_limit', 15, 2)->default(0.00);
                $table->timestamps();

                $table->index('active_organization_id');
                $table->index('price_list_id');
                $table->index('type');
            });
        }

        if (!Schema::hasTable('customer_organization')) {
            Schema::create('customer_organization', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->boolean('is_active')->default(false);
                $table->timestamps();

                $table->unique(['customer_id', 'organization_id']);
                $table->index('customer_id');
                $table->index('organization_id');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('customer_favorites')) {
            Schema::create('customer_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['customer_id', 'item_id']);
                $table->index('customer_id');
                $table->index('item_id');
            });
        }

        if (!Schema::hasTable('price_list_items')) {
            Schema::create('price_list_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->decimal('price', 10, 2);
                $table->decimal('min_quantity', 15, 3)->default(1.0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->date('effective_date')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['price_list_id', 'item_id', 'item_pack_id', 'min_quantity'], 'price_list_items_composite_unique');
                $table->index(['price_list_id', 'item_id']);
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('is_active');
                $table->index('valid_from');
                $table->index('valid_to');
            });
        }

        if (!Schema::hasTable('price_list_version_items')) {
            Schema::create('price_list_version_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('price_list_version_id')->constrained('price_list_versions')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->decimal('price', 10, 2);
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->timestamps();

                $table->unique(['price_list_version_id', 'item_id', 'item_pack_id'], 'plv_items_composite_unique');
                $table->index('item_id');
                $table->index('item_pack_id');
            });
        }

        if (!Schema::hasTable('customer_price_lists')) {
            Schema::create('customer_price_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('unassigned_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['customer_id', 'price_list_id']);
                $table->index('customer_id');
                $table->index('price_list_id');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->decimal('old_price', 12, 2);
                $table->decimal('new_price', 12, 2);
                $table->decimal('discount_percentage', 5, 2)->nullable();
                $table->string('image', 255)->nullable();
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'start_date', 'end_date']);
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('price_list_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
        Schema::dropIfExists('customer_price_lists');
        Schema::dropIfExists('price_list_version_items');
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('customer_favorites');
        Schema::dropIfExists('customer_organization');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('item_images');
        Schema::dropIfExists('item_packs');
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_models');
    }
};
