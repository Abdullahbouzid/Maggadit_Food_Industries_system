<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // عنوان إضافي (اختياري)
            $table->string('image'); // مسار الصورة
            $table->enum('type', ['home', 'category', 'product', 'promo', 'custom'])->default('custom'); // نوع البنر
            $table->string('url')->nullable(); // رابط الوجهة
            $table->boolean('show_in_customer_app')->default(true); // إظهار في التطبيق
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};