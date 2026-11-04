<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            // Basic Info
            $table->string('code', 100)->unique();
            $table->string('name_en', 255);
            $table->string('name_ar', 255);
            $table->foreignId('warehouse_type_id')->constrained('warehouse_types')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            // Contact
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('barcode', 255)->nullable();
            // Address
            $table->text('address_en')->nullable();
            $table->text('address_ar')->nullable();
            $table->string('gps_latitude', 50)->nullable();
            $table->string('gps_longitude', 50)->nullable();
            $table->string('zip_code', 20)->nullable();
            // Geographical hierarchy
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('street', 255)->nullable();
            // Destinations & Sources (JSON)
            $table->json('destinations')->nullable();   // can store array of IDs or names
            $table->json('sources')->nullable();        // same
            $table->json('employees')->nullable();      // store employee data (names/ids)
            // Status & Meta
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
