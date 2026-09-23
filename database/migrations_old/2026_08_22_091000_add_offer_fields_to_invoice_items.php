<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('offer_id')->nullable()->after('item_id')->constrained('offers')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0)->after('offer_id');
            $table->decimal('original_unit_price', 12, 2)->nullable()->after('discount_amount');
        });
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['offer_id', 'discount_amount', 'original_unit_price']);
        });
    }
};
