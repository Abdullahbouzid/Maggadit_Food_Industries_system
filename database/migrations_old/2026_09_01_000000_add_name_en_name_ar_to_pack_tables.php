<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pack_types', 'name_en')) {
            Schema::table('pack_types', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('name');
            });
        }
        if (!Schema::hasColumn('pack_types', 'name_ar')) {
            Schema::table('pack_types', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name_en');
            });
        }
        if (!Schema::hasColumn('pack_capacities', 'name_en')) {
            Schema::table('pack_capacities', function (Blueprint $table) {
                $table->string('name_en')->nullable()->after('description');
            });
        }
        if (!Schema::hasColumn('pack_capacities', 'name_ar')) {
            Schema::table('pack_capacities', function (Blueprint $table) {
                $table->string('name_ar')->nullable()->after('name_en');
            });
        }

        \DB::statement('UPDATE pack_types SET name_en = name WHERE name_en IS NULL');
        \DB::statement('UPDATE pack_types SET name_ar = name WHERE name_ar IS NULL');

        $packTypes = \DB::table('pack_types')->get();
        foreach ($packTypes as $pt) {
            if ($pt->code === 'BOX_3X3') {
                \DB::table('pack_types')->where('id', $pt->id)->update(['name_en' => 'Box 3×3', 'name_ar' => 'صندوق 3×3']);
            } elseif ($pt->code === 'BOX_4X4') {
                \DB::table('pack_types')->where('id', $pt->id)->update(['name_en' => 'Box 4×4', 'name_ar' => 'صندوق 4×4']);
            } elseif ($pt->code === 'BOX_6X6') {
                \DB::table('pack_types')->where('id', $pt->id)->update(['name_en' => 'Box 6×6', 'name_ar' => 'صندوق 6×6']);
            } elseif ($pt->code === 'PIECE') {
                \DB::table('pack_types')->where('id', $pt->id)->update(['name_en' => 'Piece', 'name_ar' => 'قطعة']);
            }
        }

        $packCapacities = \DB::table('pack_capacities')->get();
        foreach ($packCapacities as $pc) {
            $uom = \DB::table('uoms')->where('id', $pc->uom_id)->first();
            $uomNameEn = $uom ? ($uom->name_en ?? '—') : '—';
            $uomNameAr = $uom ? ($uom->name_ar ?? '—') : '—';
            \DB::table('pack_capacities')->where('id', $pc->id)->update([
                'name_en' => $pc->value . ' ' . $uomNameEn,
                'name_ar' => $pc->value . ' ' . $uomNameAr,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('pack_types', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']);
        });
        Schema::table('pack_capacities', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']);
        });
    }
};
