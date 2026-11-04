<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['order_items', 'quotation_items', 'invoice_items'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'tax_percent')) {
                    $t->decimal('tax_percent', 5, 2)->default(0)->after('discount_percent');
                }
                if (!Schema::hasColumn($table, 'tax_amount')) {
                    $t->decimal('tax_amount', 15, 2)->default(0)->after('tax_percent');
                }
            });
        }
    }

    public function down(): void {
        foreach (['order_items', 'quotation_items', 'invoice_items'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'tax_percent')) {
                    $t->dropColumn('tax_percent');
                }
                if (Schema::hasColumn($table, 'tax_amount')) {
                    $t->dropColumn('tax_amount');
                }
            });
        }
    }
};
