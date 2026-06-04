// database/migrations/2025_01_01_000001_create_vehicles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            // Basic info
            $table->string('vehicle_name');              // Vehicle Name (*)
            $table->string('vehicle_code')->unique();    // Vehicle Code (*)
            $table->string('plate_no')->unique();        // Plate No. (*)
            $table->string('barcode')->nullable();       // Barcode

            // Foreign keys
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('vehicle_type_id')->constrained()->onDelete('restrict');

            // Numeric fields
            $table->decimal('odometer', 12, 2)->nullable();    // Odometer
            $table->decimal('capacity', 12, 2)->nullable();     // Capacity
            $table->decimal('amount', 12, 2)->nullable();       // Amount
            $table->decimal('volume', 12, 2)->nullable();       // Volume
            $table->integer('min_load_qty')->nullable();        // Min Load QTY

            // Status
            $table->boolean('inactive')->default(false);        // Inactive (true = inactive)

            $table->timestamps();
            $table->softDeletes(); // optional but recommended
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};