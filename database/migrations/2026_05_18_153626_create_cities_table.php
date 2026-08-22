// database/migrations/xxxx_xx_xx_xxxxxx_create_cities_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->unique(['country_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
};