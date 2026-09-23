<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('production_batches')) {
            Schema::create('production_batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_number', 255)->unique();
                $table->date('production_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('status', 30)->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
            });
        }

        if (!Schema::hasTable('batch_items')) {
            Schema::create('batch_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('item_pack_id')->nullable()->constrained('item_packs')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->decimal('quantity', 15, 3)->default(0);
                $table->decimal('dispatched_quantity', 15, 3)->default(0);
                $table->decimal('transferred_quantity', 15, 3)->default(0);
                $table->date('expiry_date')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'item_id', 'item_pack_id'], 'batch_items_batch_item_pack_unique');
                $table->index('batch_id');
                $table->index('item_id');
                $table->index('item_pack_id');
                $table->index('warehouse_id');
                $table->index('expiry_date');
            });
        }

        if (!Schema::hasTable('batch_item_stock')) {
            Schema::create('batch_item_stock', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_item_id')->constrained('batch_items')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->decimal('quantity', 15, 3)->default(0);
                $table->decimal('reserved_quantity', 15, 3)->default(0);
                $table->timestamps();

                $table->unique(['batch_item_id', 'warehouse_id']);
                $table->index('warehouse_id');
            });
        }

        if (!Schema::hasTable('batch_movements')) {
            Schema::create('batch_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_item_id')->constrained('batch_items')->cascadeOnDelete();
                $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('movement_type', 30);
                $table->decimal('quantity', 15, 3);
                $table->date('movement_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('batch_item_id');
                $table->index('from_warehouse_id');
                $table->index('to_warehouse_id');
                $table->index('movement_date');
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('subject_en', 255);
                $table->string('subject_ar', 255);
                $table->text('body_en');
                $table->text('body_ar');
                $table->string('image', 255)->nullable();
                $table->string('video', 255)->nullable();
                $table->string('group', 50)->nullable();
                $table->string('channel', 30)->nullable();
                $table->string('icon_type', 30)->nullable();
                $table->json('customers')->nullable();
                $table->boolean('is_sent')->default(false);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamps();

                $table->index('is_sent');
                $table->index('scheduled_at');
                $table->index('channel');
            });
        }

        if (!Schema::hasTable('notification_reads')) {
            Schema::create('notification_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->timestamp('read_at')->nullable();
                $table->boolean('is_starred')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->timestamps();

                $table->unique(['notification_id', 'customer_id']);
                $table->index('notification_id');
                $table->index('customer_id');
                $table->index(['is_starred', 'is_archived']);
            });
        }

        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255)->nullable();
                $table->string('image', 255);
                $table->string('type', 30)->nullable();
                $table->string('url', 500)->nullable();
                $table->boolean('show_in_customer_app')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index('show_in_customer_app');
                $table->index('order');
            });
        }

        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->string('category', 100)->nullable();
                $table->text('question');
                $table->text('answer');
                $table->timestamps();

                $table->index('category');
            });
        }

        if (!Schema::hasTable('privacy_policies')) {
            Schema::create('privacy_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
                $table->string('type', 30)->default('privacy_policy');
                $table->string('title', 255)->nullable();
                $table->longText('content');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('type');
                $table->index('is_active');
                $table->index('organization_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_policies');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('batch_movements');
        Schema::dropIfExists('batch_item_stock');
        Schema::dropIfExists('batch_items');
        Schema::dropIfExists('production_batches');
    }
};
