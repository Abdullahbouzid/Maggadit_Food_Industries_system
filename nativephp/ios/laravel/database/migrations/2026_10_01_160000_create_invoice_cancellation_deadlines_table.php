<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_cancellation_deadlines', function (Blueprint $table) {
            $table->id();

            // The stage the invoice sits in, and the stage it would move to.
            $table->string('from_stage', 40);
            $table->string('to_stage', 40);

            // How long an invoice may stay in from_stage before cancellation
            // is no longer allowed.
            $table->unsignedInteger('hours_limit')->default(0);

            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->foreignId('updated_by')->nullable();

            $table->timestamps();

            $table->unique(['from_stage', 'to_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_cancellation_deadlines');
    }
};
