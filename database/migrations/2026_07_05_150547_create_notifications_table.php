<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('subject_en'); // العنوان بالإنجليزية
            $table->string('subject_ar'); // العنوان بالعربية
            $table->text('body_en');      // المحتوى بالإنجليزية
            $table->text('body_ar');      // المحتوى بالعربية
            $table->string('image')->nullable(); // مسار الصورة
            $table->string('video')->nullable(); // مسار الفيديو أو رابط
            $table->string('group')->nullable(); // المجموعة المستهدفة
            $table->string('channel')->default('all'); // القناة (all, email, push, sms)
            $table->json('customers')->nullable(); // قائمة العملاء (JSON)
            $table->boolean('is_sent')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
