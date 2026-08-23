<?php

// database/migrations/xxxx_xx_xx_000004_create_item_packs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uom_id')->constrained('uoms')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('barcode')->unique();
            $table->string('serial_separator')->nullable();
            $table->decimal('equivalency_factor', 12, 4)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('depth', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->boolean('is_weight_priced')->default(false);
            $table->boolean('allow_fraction_in_qty')->default(false);
            $table->boolean('is_default_pack')->default(false);
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_packs');
    }
};
