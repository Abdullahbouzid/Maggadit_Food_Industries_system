<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_lists')) {
            Schema::create('price_lists', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('status', 20)->default('active');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index('status');
                $table->index('is_default');
                $table->index('created_by');
                $table->index('currency_id');
            });
        }

        if (!Schema::hasTable('price_list_versions')) {
            Schema::create('price_list_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
                $table->date('effective_date');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->decimal('exchange_rate', 15, 6)->default(1.0);
                $table->timestamps();

                $table->index(['price_list_id', 'effective_date']);
                $table->index('is_active');
                $table->index('created_by');
                $table->index('currency_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_versions');
        Schema::dropIfExists('price_lists');
    }
};
