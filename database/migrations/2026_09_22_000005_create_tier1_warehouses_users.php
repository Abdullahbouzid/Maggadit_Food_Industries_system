<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('code', 100)->unique();
                $table->string('name_en', 255);
                $table->string('name_ar', 255);
                $table->foreignId('warehouse_type_id')->constrained('warehouse_types')->cascadeOnDelete();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('phone', 50)->nullable();
                $table->string('fax', 50)->nullable();
                $table->string('barcode', 255)->nullable();
                $table->text('address_en')->nullable();
                $table->text('address_ar')->nullable();
                $table->decimal('gps_latitude', 10, 7)->nullable();
                $table->decimal('gps_longitude', 10, 7)->nullable();
                $table->string('zip_code', 20)->nullable();
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->string('street', 255)->nullable();
                $table->json('destinations')->nullable();
                $table->json('sources')->nullable();
                $table->json('employees')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index('warehouse_type_id');
                $table->index('organization_id');
                $table->index('country_id');
                $table->index('state_id');
                $table->index('city_id');
                $table->index('area_id');
                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('organization_abouts')) {
            Schema::create('organization_abouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('vision')->nullable();
                $table->string('mission')->nullable();
                $table->json('values')->nullable();
                $table->string('image')->nullable();
                $table->string('cover_image')->nullable();
                $table->json('statistics')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('organization_id');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('subject')->nullable();
                $table->text('message');
                $table->string('status', 30)->default('new');
                $table->string('importance', 20)->default('medium');
                $table->boolean('forwarded_to_email')->default(false);
                $table->timestamp('forwarded_at')->nullable();
                $table->timestamps();

                $table->index('organization_id');
                $table->index('status');
                $table->index('importance');
            });
        }

        if (!Schema::hasTable('package_statuses')) {
            Schema::create('package_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('status_name');
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->boolean('resellable')->default(false);
                $table->boolean('expired')->default(false);
                $table->boolean('default')->default(false);
                $table->boolean('lpo_required')->default(false);
                $table->boolean('is_related_to_cn_request')->default(false);
                $table->boolean('is_price_editable')->default(false);
                $table->timestamps();

                $table->index('organization_id');
            });
        }

        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->string('name_en', 255);
                $table->string('name_ar', 255);
                $table->string('serial', 100)->unique();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->string('model_no')->nullable();
                $table->string('brand')->nullable();
                $table->text('remark')->nullable();
                $table->date('purchase_date')->nullable();
                $table->date('warranty_expiry')->nullable();
                $table->boolean('is_inactive')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index('organization_id');
                $table->index('is_inactive');
            });
        }

        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('vehicle_name');
                $table->string('vehicle_code')->unique();
                $table->string('plate_no')->unique();
                $table->string('barcode')->nullable();
                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->nullOnDelete();
                $table->decimal('odometer', 15, 2)->nullable();
                $table->decimal('capacity', 15, 2)->nullable();
                $table->decimal('amount', 15, 2)->nullable();
                $table->decimal('volume', 15, 2)->nullable();
                $table->integer('min_load_qty')->default(0);
                $table->boolean('inactive')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index('organization_id');
                $table->index('vehicle_type_id');
                $table->index('inactive');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('package_statuses');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('organization_abouts');
        Schema::dropIfExists('warehouses');
    }
};
