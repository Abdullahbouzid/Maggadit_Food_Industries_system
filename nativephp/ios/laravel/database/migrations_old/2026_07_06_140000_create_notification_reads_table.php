<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول تتبع قراءة الإشعارات لكل عميل
     * notification_id + customer_id = مفتاح أساسي مركب
     */
    public function up(): void
    {
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // كل عميل يقرأ إشعاراً مرة واحدة فقط
            $table->unique(['notification_id', 'customer_id']);

            $table->foreign('notification_id')
                ->references('id')->on('notifications')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
