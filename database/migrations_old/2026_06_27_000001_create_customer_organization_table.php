<?php

// database/migrations/2024_01_01_000001_create_customer_organization_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_organization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['customer_id', 'organization_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_organization');
    }
};
