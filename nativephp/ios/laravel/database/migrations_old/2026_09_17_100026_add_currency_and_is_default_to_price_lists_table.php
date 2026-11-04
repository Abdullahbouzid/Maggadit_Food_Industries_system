<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('price_lists', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('created_by')
                    ->constrained('currencies')->nullOnDelete();
            }
            if (!Schema::hasColumn('price_lists', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('currency_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            if (Schema::hasColumn('price_lists', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('price_lists', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
};
