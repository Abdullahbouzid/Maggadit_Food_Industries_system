<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_deadline_settings', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type');
            $table->integer('hours_limit');
            $table->boolean('is_active')->default(true);
            $table->string('applies_to')->default('all');
            $table->json('customer_ids')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE payment_deadline_settings ADD CONSTRAINT chk_payment_type CHECK (payment_type IN ('cash', 'credit'))");
        DB::statement("ALTER TABLE payment_deadline_settings ADD CONSTRAINT chk_applies_to CHECK (applies_to IN ('all', 'specific_customers'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_deadline_settings');
    }
};
