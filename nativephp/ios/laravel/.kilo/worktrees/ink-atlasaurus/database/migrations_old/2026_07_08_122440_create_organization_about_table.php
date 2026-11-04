<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organization_abouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // الحقول الرئيسية كما طلبت (عناوين رئيسية كأعمدة)
            $table->string('title')->nullable();              // العنوان الرئيسي
            $table->text('description')->nullable();          // الوصف / القصة
            $table->string('vision')->nullable();             // الرؤية
            $table->string('mission')->nullable();            // الرسالة
            $table->json('values')->nullable();               // القيم (مصفوفة)
            $table->string('image')->nullable();              // صورة الشعار أو الخلفية
            $table->string('cover_image')->nullable();        // صورة الغلاف
            $table->json('statistics')->nullable();           // إحصائيات (مثلاً: {'years':15, 'products':200})
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_abouts');
    }
};
