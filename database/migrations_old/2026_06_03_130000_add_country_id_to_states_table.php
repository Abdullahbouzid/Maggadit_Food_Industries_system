<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('code')->constrained('countries')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropForeignKey(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
