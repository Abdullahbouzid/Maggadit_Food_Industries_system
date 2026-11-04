<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('item_types')) {
            Schema::create('item_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('division');
                $table->boolean('show_in_customer_app')->default(false);
                $table->string('image')->nullable();
                $table->timestamps();

                $table->index('show_in_customer_app');
            });
        }

        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('logo')->nullable();
                $table->boolean('show_in_customer_app')->default(false);
                $table->timestamps();

                $table->index('show_in_customer_app');
            });
        }

        if (!Schema::hasTable('uoms')) {
            Schema::create('uoms', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name_en');
                $table->string('name_ar');
                $table->string('uom_symbol')->nullable();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->timestamps();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }

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

        if (!Schema::hasTable('pack_types')) {
            Schema::create('pack_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('name_en')->nullable();
                $table->string('name_ar')->nullable();
                $table->integer('pieces_per_pack')->default(1);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pack_capacities')) {
            Schema::create('pack_capacities', function (Blueprint $table) {
                $table->id();
                $table->decimal('value', 10, 2);
                $table->foreignId('uom_id')->constrained('uoms')->cascadeOnDelete();
                $table->string('name_en')->nullable();
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('uom_id');
            });
        }

        if (!Schema::hasTable('pack_operations')) {
            Schema::create('pack_operations', function (Blueprint $table) {
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

        if (!Schema::hasTable('pack_groups')) {
            Schema::create('pack_groups', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_groups');
        Schema::dropIfExists('pack_operations');
        Schema::dropIfExists('pack_capacities');
        Schema::dropIfExists('pack_types');
        Schema::dropIfExists('item_models');
        Schema::dropIfExists('uoms');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('item_types');
    }
};
