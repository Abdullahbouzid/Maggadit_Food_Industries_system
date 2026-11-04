<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per batch + pack + warehouse minimum. Without this, low-stock can
        // only be judged per item and a healthy batch masks a depleted one.
        Schema::table('batch_item_stock', function (Blueprint $table) {
            $table->decimal('low_stock_threshold', 14, 3)->default(0)->after('reserved_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('batch_item_stock', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
