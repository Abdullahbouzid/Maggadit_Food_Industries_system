<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores two pivot tables that the ItemPack model's belongsToMany
 * relations require:
 *
 *   ItemPack::packOperations() -> item_pack_pack_operation
 *   ItemPack::packGroups()     -> item_pack_pack_group
 *
 * Both were defined by migrations under database/migrations_old/ but were
 * never applied to the database, so every page that eager-loads
 * `packs.packOperations` (items.show, items.edit, invoices.show, ...) failed
 * with "Table 'item_pack_pack_operation' doesn't exist".
 *
 * The migrations are written defensively: they only build what is missing,
 * so this is safe to run against databases that already have the tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_pack_pack_operation')) {
            Schema::create('item_pack_pack_operation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_pack_id')->constrained('item_packs')->cascadeOnDelete();
                $table->foreignId('pack_operation_id')->constrained('pack_operations')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['item_pack_id', 'pack_operation_id'], 'item_pack_pack_operation_unique');
            });
        }

        if (! Schema::hasTable('item_pack_pack_group')) {
            Schema::create('item_pack_pack_group', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_pack_id')->constrained('item_packs')->cascadeOnDelete();
                $table->foreignId('pack_group_id')->constrained('pack_groups')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['item_pack_id', 'pack_group_id'], 'item_pack_pack_group_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_pack_pack_operation');
        Schema::dropIfExists('item_pack_pack_group');
    }
};
