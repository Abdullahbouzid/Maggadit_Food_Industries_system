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
        if (!Schema::hasTable('pack_capacities')) {
            Schema::create('pack_capacities', function (Blueprint $table) {
                $table->id();
                $table->decimal('value', 10, 2);
                $table->foreignId('uom_id')->constrained()->cascadeOnDelete();
                $table->string('name_en')->nullable();
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['value', 'uom_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pack_capacities');
    }
};
