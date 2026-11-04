<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // Category Code (*)
            $table->string('name');                    // Category Name (*)
            $table->string('division');                // Division (*)
            $table->boolean('show_in_customer_app')->default(false); // Show in Customer App
            $table->string('image')->nullable();       // img
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
