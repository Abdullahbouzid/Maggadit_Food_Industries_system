<?php
// database/migrations/2025_01_01_000001_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // بيانات أساسية
            $table->string('employee_name');
            $table->string('employee_code')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->string('employee_type')->default('Employee'); // Employee, etc.
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('national_id')->nullable();
            $table->boolean('on_hold')->default(false);
            $table->boolean('inactive')->default(false);
            $table->boolean('out_source')->default(false);

            // بيانات الوصول والتنظيمات
            $table->text('organization_access')->nullable();   // JSON أو نص
            $table->string('manager_level')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('divisions')->nullable();             // JSON
            $table->string('default_division')->nullable();
            $table->string('device')->nullable();
            $table->string('vehicle')->nullable();
            $table->string('territory')->nullable();
            $table->string('driver')->nullable();
            $table->string('helper')->nullable();
            $table->string('region')->nullable();
            $table->string('channel')->nullable();
            $table->text('items_list')->nullable();            // JSON
            $table->foreignId('employee_branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('contact_country_id')->nullable()->constrained('countries')->onDelete('set null');

            // بيانات الأمان
            $table->string('user_name')->unique()->nullable();
            $table->string('password')->nullable();    // hashed
            // الأمان: مجموعات الأمان (many-to-many via pivot)

            // البيانات المالية
            $table->string('currency')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Document Sequence (جميع الحقول المطلوبة)
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('transaction_credit_note')->nullable();
            $table->unsignedBigInteger('transaction_debit_note')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('warehouse_transaction_id')->nullable();
            $table->unsignedBigInteger('return_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('sample_id')->nullable();
            $table->unsignedBigInteger('applied_payment_id')->nullable();
            $table->unsignedBigInteger('replenish_id')->nullable();
            $table->unsignedBigInteger('remove_id')->nullable();
            $table->unsignedBigInteger('new_customer_code')->nullable();
            $table->unsignedBigInteger('settlement_id')->nullable();
            $table->unsignedBigInteger('order_invoice_id')->nullable();
            $table->unsignedBigInteger('return_order_id')->nullable();
            $table->unsignedBigInteger('contracted_foc_id')->nullable();
            $table->unsignedBigInteger('unlimited_foc_id')->nullable();
            $table->unsignedBigInteger('fixed_incentive_id')->nullable();
            $table->unsignedBigInteger('variable_incentive_id')->nullable();
            $table->unsignedBigInteger('organization_credit_note')->nullable();
            $table->unsignedBigInteger('organization_debit_note')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('prison_foc_id')->nullable();
            $table->unsignedBigInteger('guarantees_foc_id')->nullable();
            $table->unsignedBigInteger('gift_voucher_id')->nullable();
            $table->unsignedBigInteger('icash_transaction_id')->nullable();
            $table->unsignedBigInteger('icash_reversal_id')->nullable();
            $table->unsignedBigInteger('delivery_note_id')->nullable();
            $table->unsignedBigInteger('promotion_transaction_id')->nullable();
            $table->unsignedBigInteger('max_customer_code')->nullable();
            $table->unsignedBigInteger('credit_note_request')->nullable();
            $table->unsignedBigInteger('customer_app_order_id')->nullable();
            $table->unsignedBigInteger('applied_e_payment_id')->nullable();
            $table->unsignedBigInteger('customer_app_credit_note_id')->nullable();
            $table->unsignedBigInteger('customer_app_down_payment_id')->nullable();

            $table->timestamps();
            $table->softDeletes(); // اختياري
        });

        // جدول وسيط لمجموعات الأمان (Security Groups)
        Schema::create('employee_security_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('security_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_security_group');
        Schema::dropIfExists('employees');
    }
};