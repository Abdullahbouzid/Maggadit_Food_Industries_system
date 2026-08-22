<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 255);
            $table->string('name_ar', 255);
            $table->string('serial', 100)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('model_no', 255)->nullable();
            $table->string('brand', 255)->nullable(); // ماركة الجهاز (نصي)
            $table->text('remark')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
};