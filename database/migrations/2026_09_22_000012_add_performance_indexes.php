<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // orders: missing deleted_at, warehouse_id
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['customer_id', 'status'], 'orders_customer_status_idx');
                $table->index(['status', 'created_at'], 'orders_status_created_idx');
                $table->index('deleted_at', 'orders_deleted_at_idx');
                $table->index('warehouse_id', 'orders_warehouse_id_idx');
            });
        }

        // invoices: missing status, deleted_at, payment_deadline_at
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['customer_id', 'status'], 'invoices_customer_status_idx');
            });

            DB::statement("CREATE INDEX IF NOT EXISTS invoices_status_deadline_idx ON invoices (status, payment_deadline_at)");
            DB::statement("CREATE INDEX IF NOT EXISTS invoices_deleted_at_idx ON invoices (deleted_at)");
            DB::statement("CREATE INDEX IF NOT EXISTS invoices_payment_deadline_idx ON invoices (payment_deadline_at)");
        }

        // deliveries: missing invoice_id, customer_id, current_stage
        if (Schema::hasTable('deliveries')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->index('invoice_id', 'deliveries_invoice_id_idx');
                $table->index('customer_id', 'deliveries_customer_id_idx');
                $table->index('current_stage', 'deliveries_current_stage_idx');
            });
        }

        // return_requests: missing invoice_id
        if (Schema::hasTable('return_requests')) {
            Schema::table('return_requests', function (Blueprint $table) {
                $table->index('invoice_id', 'return_requests_invoice_id_idx');
            });
        }

        // price_list_items: missing is_active
        if (Schema::hasTable('price_list_items')) {
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->index('is_active', 'price_list_items_is_active_idx');
            });
        }

        // batch_items: missing expiry_date
        if (Schema::hasTable('batch_items')) {
            Schema::table('batch_items', function (Blueprint $table) {
                $table->index('expiry_date', 'batch_items_expiry_date_idx');
            });
        }

        // items: missing is_inactive, deleted_at
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $table->index(['category_id', 'is_inactive'], 'items_category_inactive_idx');
                $table->index(['brand_id', 'is_inactive'], 'items_brand_inactive_idx');
                $table->index(['item_type_id', 'is_inactive'], 'items_type_inactive_idx');
                $table->index('is_inactive', 'items_inactive_idx');
                $table->index('deleted_at', 'items_deleted_at_idx');
            });
        }

        // customers: missing deleted_at
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('price_list_id', 'customers_price_list_idx');
            });
        }

        // users: missing deleted_at
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email_verified_at', 'users_email_verified_idx');
            });
        }

        // invoice_items: composite indexes
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index(['invoice_id', 'item_id'], 'invoice_items_inv_item_idx');
                $table->index('warehouse_id', 'invoice_items_warehouse_idx');
            });
        }

        // order_items: composite indexes
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->index(['order_id', 'item_id'], 'order_items_order_item_idx');
                $table->index('warehouse_id', 'order_items_warehouse_idx');
            });
        }

        // payments: indexes for performance
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['invoice_id', 'paid_at'], 'payments_invoice_paid_idx');
                $table->index('customer_id', 'payments_customer_idx');
                $table->index('paid_at', 'payments_paid_at_idx');
            });
        }

        // deliveries: composite indexes
        if (Schema::hasTable('deliveries')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->index(['driver_id', 'current_stage'], 'deliveries_driver_stage_idx');
            });
        }

        // return_items: indexes
        if (Schema::hasTable('return_items')) {
            Schema::table('return_items', function (Blueprint $table) {
                $table->index('return_request_id', 'return_items_request_idx');
                $table->index('invoice_item_id', 'return_items_inv_item_idx');
            });
        }

        // inventories: composite indexes
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->index(['item_id', 'warehouse_id'], 'inventories_item_warehouse_idx');
                $table->index('reorder_level', 'inventories_reorder_idx');
            });
        }

        // inventory_movements: composite indexes
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->index(['item_id', 'warehouse_id', 'created_at'], 'inv_mov_item_wh_created_idx');
                $table->index('batch_item_id', 'inventory_movements_batch_idx');
                $table->index('type', 'inventory_movements_type_idx');
                $table->index('created_at', 'inventory_movements_created_idx');
            });
        }

        // stock_reservations: composite indexes
        if (Schema::hasTable('stock_reservations')) {
            Schema::table('stock_reservations', function (Blueprint $table) {
                $table->index(['status', 'expires_at'], 'stock_res_status_expires_idx');
                $table->index('item_id', 'stock_res_item_idx');
                $table->index('warehouse_id', 'stock_res_warehouse_idx');
            });
        }

        // wallet_transactions: composite indexes
        if (Schema::hasTable('wallet_transactions')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->index(['wallet_id', 'created_at'], 'wallet_trans_wallet_created_idx');
                $table->index('type', 'wallet_trans_type_idx');
            });
        }

        // customer_wallets: is_active index
        if (Schema::hasTable('customer_wallets')) {
            Schema::table('customer_wallets', function (Blueprint $table) {
                $table->index('is_active', 'customer_wallets_active_idx');
            });
        }

        // audit_logs: indexes
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['event', 'created_at'], 'audit_logs_event_created_idx');
                $table->index('user_id', 'audit_logs_user_idx');
            });
        }

        // price_list_items: composite index
        if (Schema::hasTable('price_list_items')) {
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->index(['price_list_id', 'item_id', 'is_active'], 'pli_price_item_active_idx');
            });
        }

        // batch_item_stock: composite
        if (Schema::hasTable('batch_item_stock')) {
            Schema::table('batch_item_stock', function (Blueprint $table) {
                $table->index(['warehouse_id', 'quantity'], 'batch_stock_warehouse_qty_idx');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'orders', 'order_items', 'invoices', 'invoice_items', 'payments',
            'deliveries', 'return_requests', 'return_items', 'inventories',
            'inventory_movements', 'stock_reservations', 'wallet_transactions',
            'customer_wallets', 'audit_logs', 'price_list_items', 'batch_items',
            'batch_item_stock', 'items', 'customers', 'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Indices are dropped with the table; no-op here
                });
            }
        }
    }
};
