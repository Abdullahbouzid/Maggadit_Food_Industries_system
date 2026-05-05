<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('u_o_m_s', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->decimal('conversion_factor', 10, 4)->default(1);
            $table->unsignedBigInteger('base_uom_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('base_uom_id')->references('id')->on('u_o_m_s')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('u_o_m_s');
    }
};