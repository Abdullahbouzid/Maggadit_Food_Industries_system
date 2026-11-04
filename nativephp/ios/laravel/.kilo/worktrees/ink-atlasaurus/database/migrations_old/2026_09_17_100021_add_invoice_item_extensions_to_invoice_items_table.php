<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('item_pack_id')->nullable()->after('item_id')->change();
            $table->foreignId('batch_item_id')->nullable()->after('warehouse_id')->constrained('batch_items')->nullOnDelete();
            $table->decimal('discount_percent', 5, 2)->default(0)->after('subtotal');
            $table->string('price_source')->nullable()->after('discount_percent');
            $table->foreignId('price_list_item_id')->nullable()->after('price_list_id')->constrained('price_list_items')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->after('price_list_item_id')->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 15, 6)->default(1.0)->after('currency_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['batch_item_id']);
            $table->dropForeign(['price_list_item_id']);
            $table->dropForeign(['currency_id']);

            $table->dropColumn([
                'batch_item_id', 'discount_percent', 'price_source',
                'price_list_item_id', 'currency_id', 'exchange_rate',
            ]);
        });
    }
};
