<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Remove foreign key from item_packs to pack_definitions
        Schema::table('item_packs', function (Blueprint $table) {
            if (Schema::hasColumn('item_packs', 'pack_definition_id')) {
                $table->dropForeign(['pack_definition_id']);
                $table->dropColumn('pack_definition_id');
            }
        });

        // Step 2: Drop pivot tables referencing pack_definitions
        Schema::dropIfExists('pack_definition_pack_operation');
        Schema::dropIfExists('pack_definition_pack_group');

        // Step 3: Drop pack_definitions table (parent table)
        Schema::dropIfExists('pack_definitions');
    }

    public function down(): void
    {
        // Recreate pack_definitions table
        Schema::create('pack_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('code')->nullable()->unique();
            $table->foreignId('pack_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pack_capacity_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_in_pack')->default(1);
            $table->string('barcode')->nullable()->unique();
            $table->string('serial_separator')->nullable();
            $table->decimal('equivalency_factor', 12, 4)->nullable();
            $table->decimal('height', 12, 4)->nullable();
            $table->decimal('width', 12, 4)->nullable();
            $table->decimal('depth', 12, 4)->nullable();
            $table->decimal('weight', 12, 4)->nullable();
            $table->boolean('is_weight_priced')->default(false);
            $table->boolean('allow_fraction_in_qty')->default(false);
            $table->boolean('is_default_pack')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Recreate pivot tables
        Schema::create('pack_definition_pack_operation', function (Blueprint $table) {
            $table->foreignId('pack_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pack_operation_id')->constrained()->cascadeOnDelete();
            $table->primary(['pack_definition_id', 'pack_operation_id']);
        });

        Schema::create('pack_definition_pack_group', function (Blueprint $table) {
            $table->foreignId('pack_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pack_group_id')->constrained()->cascadeOnDelete();
            $table->primary(['pack_definition_id', 'pack_group_id']);
        });

        // Restore pack_definition_id on item_packs
        Schema::table('item_packs', function (Blueprint $table) {
            $table->foreignId('pack_definition_id')->nullable()->after('item_id')->constrained('pack_definitions')->nullOnDelete();
        });
    }
};
