<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->default('cash')->after('password');
            $table->decimal('credit_limit', 15, 2)->default(0)->after('customer_type');
            $table->decimal('current_balance', 15, 2)->default(0)->after('credit_limit');
            $table->integer('payment_terms_days')->default(0)->after('current_balance');
            $table->boolean('wallet_enabled')->default(false)->after('payment_terms_days');
        });

        DB::statement("ALTER TABLE customers ADD CONSTRAINT chk_customer_type CHECK (customer_type IN ('cash', 'credit'))");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_type', 'credit_limit', 'current_balance',
                'payment_terms_days', 'wallet_enabled',
            ]);
        });
    }
};
