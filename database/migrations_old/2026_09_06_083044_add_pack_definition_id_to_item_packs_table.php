<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_packs', function (Blueprint $table) {
            $table->foreignId('pack_definition_id')->nullable()->after('item_id')->constrained('pack_definitions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('item_packs', function (Blueprint $table) {
            $table->dropForeign(['pack_definition_id']);
            $table->dropColumn('pack_definition_id');
        });
    }
};