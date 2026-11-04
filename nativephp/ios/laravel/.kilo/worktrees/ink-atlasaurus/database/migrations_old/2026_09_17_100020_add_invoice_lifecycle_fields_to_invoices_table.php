<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('price_list_id')->constrained('orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->after('order_id')->constrained('quotations')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'payment_type')) {
                $table->string('payment_type')->default('cash')->after('status');
            }
            if (!Schema::hasColumn('invoices', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('payment_type')->constrained('currencies')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.0)->after('currency_id');
            }
            if (!Schema::hasColumn('invoices', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('invoices', 'payment_deadline_at')) {
                $table->timestamp('payment_deadline_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_deadline_at');
            }
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
        });

        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoice_payment_type CHECK (payment_type IN ('cash', 'credit'))");
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoice_status CHECK (status IN ('pending_approval', 'approved', 'rejected', 'paid', 'partially_paid', 'cancelled', 'expired'))");

        // Add indexes for performance
        DB::statement("CREATE INDEX IF NOT EXISTS idx_invoices_status_deadline ON invoices (status, payment_deadline_at)");
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS chk_invoice_payment_type");
            DB::statement("ALTER TABLE invoices DROP CONSTRAINT IF EXISTS chk_invoice_status");

            $table->dropForeign(['order_id']);
            $table->dropForeign(['quotation_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'order_id', 'quotation_id', 'payment_type', 'currency_id',
                'exchange_rate', 'approved_by', 'approved_at', 'payment_deadline_at',
                'paid_amount', 'paid_at', 'due_date', 'payment_method', 'payment_reference',
            ]);
        });
    }
};
