<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrate useful fields from pack_definitions to item_packs
     * before the pack_definitions table is dropped.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pack_definitions') || !Schema::hasTable('item_packs')) {
            return;
        }

        $packDefinitions = DB::table('pack_definitions')->get();

        foreach ($packDefinitions as $packDef) {
            DB::table('item_packs')
                ->where('pack_definition_id', $packDef->id)
                ->update([
                    'quantity' => $packDef->quantity_in_pack ?? 1,
                    'equivalency_factor' => $packDef->equivalency_factor ?? null,
                    'height' => $packDef->height ?? null,
                    'width' => $packDef->width ?? null,
                    'depth' => $packDef->depth ?? null,
                    'weight' => $packDef->weight ?? null,
                    'is_weight_priced' => $packDef->is_weight_priced ?? false,
                    'allow_fraction_in_qty' => $packDef->allow_fraction_in_qty ?? false,
                    'is_default_pack' => $packDef->is_default_pack ?? false,
                    'sort_order' => $packDef->sort_order ?? 0,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // No rollback needed - this is a one-way data migration
    }
};
