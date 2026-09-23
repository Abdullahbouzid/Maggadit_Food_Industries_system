<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number', 255)->unique();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->string('method', 30)->nullable();
                $table->string('reference', 255)->nullable();
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('customer_id');
                $table->index('paid_at');
            });
        }

        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('current_stage', 30)->default('shipping');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('invoice_id');
                $table->index('order_id');
                $table->index('driver_id');
                $table->index('current_stage');
            });
        }

        if (!Schema::hasTable('delivery_stage_logs')) {
            Schema::create('delivery_stage_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
                $table->string('stage', 40);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->text('notes')->nullable();
                $table->string('photo_path', 255)->nullable();
                $table->timestamp('created_at');

                $table->index(['delivery_id', 'created_at']);
                $table->index('updated_by');
            });
        }

        if (!Schema::hasTable('return_requests')) {
            Schema::create('return_requests', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 255)->unique();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('type', 20)->default('partial');
                $table->text('reason')->nullable();
                $table->string('status', 20)->default('pending');
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->decimal('total_refund', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('invoice_id');
                $table->index('order_id');
                $table->index('customer_id');
                $table->index('status');
                $table->index('requested_by');
                $table->index('reviewed_by');
            });
        }

        if (!Schema::hasTable('return_items')) {
            Schema::create('return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
                $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('batch_item_id')->nullable()->constrained('batch_items')->nullOnDelete();
                $table->decimal('quantity_returned', 15, 3);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('refund_amount', 15, 2)->default(0);
                $table->string('condition', 20)->default('good');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('return_request_id');
                $table->index('invoice_item_id');
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('batch_item_id');
            });
        }

        if (!Schema::hasTable('credit_notes')) {
            Schema::create('credit_notes', function (Blueprint $table) {
                $table->id();
                $table->string('credit_note_number', 255)->unique();
                $table->foreignId('return_request_id')->nullable()->constrained('return_requests')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamp('issued_at')->nullable();
                $table->foreignId('applied_to_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('customer_id');
                $table->index('currency_id');
                $table->index('applied_to_invoice_id');
            });
        }

        if (!Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->decimal('quantity_on_hand', 15, 3)->default(0);
                $table->decimal('reserved_quantity', 15, 3)->default(0);
                $table->decimal('damaged_quantity', 15, 3)->default(0);
                $table->decimal('reorder_level', 15, 3)->default(0);
                $table->timestamp('last_counted_at')->nullable();
                $table->timestamps();

                $table->unique(['item_id', 'item_pack_id', 'warehouse_id']);
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('warehouse_id');
            });
        }

        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('batch_item_id')->nullable()->constrained('batch_items')->nullOnDelete();
                $table->string('type', 30);
                $table->decimal('quantity', 15, 3);
                $table->string('reference_type', 255)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('balance_after', 15, 3)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('created_at');

                $table->index('item_id');
                $table->index('warehouse_id');
                $table->index('batch_item_id');
                $table->index(['reference_type', 'reference_id']);
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('stock_reservations')) {
            Schema::create('stock_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->decimal('quantity', 15, 3);
                $table->string('reserved_for_type', 255)->nullable();
                $table->unsignedBigInteger('reserved_for_id')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->index(['reserved_for_type', 'reserved_for_id']);
                $table->index('status');
                $table->index('expires_at');
            });
        }

        if (!Schema::hasTable('payment_deadline_settings')) {
            Schema::create('payment_deadline_settings', function (Blueprint $table) {
                $table->id();
                $table->string('payment_type', 20);
                $table->integer('hours_limit')->default(48);
                $table->boolean('is_active')->default(true);
                $table->string('applies_to', 30)->default('all');
                $table->json('customer_ids')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('payment_type');
                $table->index('is_active');
                $table->index('updated_by');
            });
        }

        if (!Schema::hasTable('customer_wallets')) {
            Schema::create('customer_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('currency_id')->constrained('currencies');
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('total_deposited', 15, 2)->default(0);
                $table->decimal('total_spent', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('customer_id');
                $table->index('currency_id');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('customer_wallets')->cascadeOnDelete();
                $table->string('type', 30);
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2);
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->string('reference_type', 255)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('method', 30)->nullable();
                $table->string('reference_number', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('created_at');

                $table->index('wallet_id');
                $table->index(['reference_type', 'reference_id']);
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('auditable_type', 255)->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->string('event', 40);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_type', 60)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at');

                $table->index(['auditable_type', 'auditable_id']);
                $table->index(['event', 'created_at']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('customer_wallets');
        Schema::dropIfExists('payment_deadline_settings');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('delivery_stage_logs');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('payments');
    }
};
