<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('vendor_name');
                $table->string('code')->unique();
                $table->string('phone')->nullable();
                $table->string('fax')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_number')->nullable();
                $table->text('street')->nullable();
                $table->decimal('commission_percentage', 5, 2)->default(0);
                $table->boolean('inactive')->default(false);
                $table->timestamps();

                $table->index('inactive');
            });
        }

        if (!Schema::hasTable('vehicle_types')) {
            Schema::create('vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name_en');
                $table->string('name_ar');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('warehouse_types')) {
            Schema::create('warehouse_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name_en');
                $table->string('name_ar');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('sales_order_types')) {
            Schema::create('sales_order_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 50)->unique();
                $table->boolean('active')->default(true);
                $table->boolean('default')->default(false);
                $table->timestamps();

                $table->index('active');
                $table->index('default');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_types');
        Schema::dropIfExists('warehouse_types');
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('vendors');
    }
};
