<?php

// database/migrations/xxxx_xx_xx_000005_create_item_pack_pack_operation_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_pack_pack_operation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pack_operation_id')->constrained('pack_operations')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_pack_pack_operation');
    }
};
