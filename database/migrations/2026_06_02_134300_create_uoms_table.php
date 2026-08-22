<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_uoms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uoms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('uom_symbol')->nullable(); // مثال: kg, m, pc, L
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uoms');
    }
};