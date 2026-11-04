<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 255)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('status', 30)->default('new');
                $table->foreignId('price_list_snapshot_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->text('delivery_address')->nullable();
                $table->string('delivery_phone', 30)->nullable();
                $table->date('scheduled_delivery_date')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('shipping_fee', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('customer_id');
                $table->index('status');
                $table->index('warehouse_id');
                $table->index('deleted_at');
                $table->index('created_by');
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->decimal('quantity', 15, 3);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('line_total', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->string('price_source', 30)->nullable();
                $table->foreignId('price_list_item_id')->nullable()->constrained('price_list_items')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('order_id');
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('warehouse_id');
                $table->index('price_list_item_id');
            });
        }

        if (!Schema::hasTable('order_status_histories')) {
            Schema::create('order_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40);
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['order_id', 'created_at']);
                $table->index('changed_by');
            });
        }

        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->string('quotation_number', 255)->unique();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->integer('version')->default(1);
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->string('status', 30)->default('draft');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->date('valid_until')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->string('customer_response', 30)->default('pending');
                $table->timestamp('customer_response_at')->nullable();
                $table->text('customer_notes')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['order_id', 'version']);
                $table->index('customer_id');
                $table->index('status');
                $table->index('valid_until');
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('quotation_items')) {
            Schema::create('quotation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->decimal('quantity', 15, 3);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('line_total', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->string('item_name_snapshot', 255)->nullable();
                $table->string('pack_name_snapshot', 255)->nullable();
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->foreignId('price_history_id')->nullable()->constrained('price_list_version_items')->nullOnDelete();
                $table->timestamps();

                $table->index('quotation_id');
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('warehouse_id');
                $table->index('price_list_id');
                $table->index('price_history_id');
            });
        }

        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 255)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
                $table->date('invoice_date');
                $table->string('payment_type', 20)->default('cash');
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('status', 30)->default('pending_approval');
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('payment_deadline_at')->nullable();
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->timestamp('paid_at')->nullable();
                $table->date('due_date')->nullable();
                $table->string('payment_method', 30)->nullable();
                $table->string('payment_reference', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('customer_id');
                $table->index('status');
                $table->index('deleted_at');
                $table->index('price_list_id');
                $table->index('approved_by');
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('batch_item_id')->nullable()->constrained('batch_items')->nullOnDelete();
                $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
                $table->decimal('quantity', 15, 3);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('original_unit_price', 12, 2)->nullable();
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('line_total', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->string('price_source', 30)->nullable();
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->foreignId('price_history_id')->nullable()->constrained('price_list_version_items')->nullOnDelete();
                $table->foreignId('price_list_item_id')->nullable()->constrained('price_list_items')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('warehouse_id');
                $table->index('batch_item_id');
                $table->index('offer_id');
                $table->index('price_list_id');
                $table->index('price_history_id');
                $table->index('price_list_item_id');
            });
        }

        if (!Schema::hasTable('invoice_item_batches')) {
            Schema::create('invoice_item_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
                $table->foreignId('batch_item_id')->constrained('batch_items')->cascadeOnDelete();
                $table->decimal('quantity', 15, 3)->default(0);
                $table->decimal('unit_cost', 15, 2)->nullable();
                $table->timestamps();

                $table->index('invoice_item_id');
                $table->index('batch_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_item_batches');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
