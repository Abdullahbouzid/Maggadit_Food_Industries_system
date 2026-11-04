<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasPackType = \DB::select("SHOW COLUMNS FROM item_packs LIKE 'pack_type_id'");
        $hasPackCapacity = \DB::select("SHOW COLUMNS FROM item_packs LIKE 'pack_capacity_id'");
        $hasQuantityInPack = \DB::select("SHOW COLUMNS FROM item_packs LIKE 'quantity_in_pack'");
        $hasUom = \DB::select("SHOW COLUMNS FROM item_packs LIKE 'uom_id'");
        $hasQuantity = \DB::select("SHOW COLUMNS FROM item_packs LIKE 'quantity'");

        if (empty($hasPackType)) {
            Schema::table('item_packs', function (Blueprint $table) {
                $table->foreignId('pack_type_id')->after('item_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
        
        if (empty($hasPackCapacity)) {
            Schema::table('item_packs', function (Blueprint $table) {
                $table->foreignId('pack_capacity_id')->after('pack_type_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
        
        if (empty($hasQuantityInPack)) {
            Schema::table('item_packs', function (Blueprint $table) {
                $table->integer('quantity_in_pack')->after('pack_capacity_id')->default(1);
            });
        }
        
        if (!empty($hasUom)) {
            \DB::statement('ALTER TABLE item_packs DROP FOREIGN KEY IF EXISTS item_packs_uom_id_foreign');
            \DB::statement('ALTER TABLE item_packs DROP COLUMN uom_id');
        }
        
        if (!empty($hasQuantity)) {
            \DB::statement('ALTER TABLE item_packs DROP COLUMN quantity');
        }
        
        \DB::table('pack_types')->insertOrIgnore([
            ['name' => 'صندوق 3*3', 'code' => 'BOX_3X3', 'pieces_per_pack' => 9, 'description' => 'صندوق 3×3 يحتوي على 9 قطع', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'صندوق 4*4', 'code' => 'BOX_4X4', 'pieces_per_pack' => 16, 'description' => 'صندوق 4×4 يحتوي على 16 قطعة', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'صندوق 6*6', 'code' => 'BOX_6X6', 'pieces_per_pack' => 36, 'description' => 'صندوق 6×6 يحتوي على 36 قطعة', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قطعة', 'code' => 'PIECE', 'pieces_per_pack' => 1, 'description' => 'قطعة واحدة', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        $gramUom = \DB::table('uoms')->where('code', 'PCS')->first();
        $kgUom = \DB::table('uoms')->where('code', 'KG')->first();
        $lUom = \DB::table('uoms')->where('code', 'L')->first();
        
        $capacities = [];
        if ($gramUom) {
            $capacities[] = ['value' => 500, 'uom_id' => $gramUom->id, 'description' => '500 جرام', 'created_at' => now(), 'updated_at' => now()];
            $capacities[] = ['value' => 250, 'uom_id' => $gramUom->id, 'description' => '250 جرام', 'created_at' => now(), 'updated_at' => now()];
        }
        if ($kgUom) {
            $capacities[] = ['value' => 1, 'uom_id' => $kgUom->id, 'description' => '1 كيلو', 'created_at' => now(), 'updated_at' => now()];
        }
        if ($lUom) {
            $capacities[] = ['value' => 1, 'uom_id' => $lUom->id, 'description' => '1 لتر', 'created_at' => now(), 'updated_at' => now()];
            $capacities[] = ['value' => 250, 'uom_id' => $lUom->id, 'description' => '250 مللي', 'created_at' => now(), 'updated_at' => now()];
            $capacities[] = ['value' => 440, 'uom_id' => $lUom->id, 'description' => '440 مللي', 'created_at' => now(), 'updated_at' => now()];
            $capacities[] = ['value' => 500, 'uom_id' => $lUom->id, 'description' => '500 مللي', 'created_at' => now(), 'updated_at' => now()];
        }
        
        foreach ($capacities as $capacity) {
            \DB::table('pack_capacities')->insertOrIgnore($capacity);
        }
        
        $defaultPackType = \DB::table('pack_types')->where('code', 'PIECE')->first();
        $defaultCapacity = \DB::table('pack_capacities')->where('value', 500)->first();
        
        if ($defaultPackType && $defaultCapacity) {
            \DB::table('item_packs')->update([
                'pack_type_id' => $defaultPackType->id,
                'pack_capacity_id' => $defaultCapacity->id,
                'quantity_in_pack' => 1,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('item_packs', function (Blueprint $table) {
            $table->dropForeign(['pack_capacity_id']);
            $table->dropForeign(['pack_type_id']);
            $table->dropColumn(['pack_capacity_id', 'pack_type_id', 'quantity_in_pack']);
            
            $table->unsignedBigInteger('uom_id')->after('item_id');
            $table->integer('quantity')->after('uom_id');
            
            $table->foreign('uom_id')->references('id')->on('uoms')->cascadeOnDelete();
        });
    }
};