<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores the `competitors` table.
 *
 * The model (App\Models\Competitor), controller, import and export classes
 * are all present and wired into routes, but the original migration lived
 * under database/migrations_old/ and was never applied, so every Competitors
 * page failed with "Table 'competitors' doesn't exist".
 *
 * The column set matches Competitor::$fillable exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competitors')) {
            return;
        }

        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('competitor_type')->nullable();
            $table->string('gln')->nullable()->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitors');
    }
};
