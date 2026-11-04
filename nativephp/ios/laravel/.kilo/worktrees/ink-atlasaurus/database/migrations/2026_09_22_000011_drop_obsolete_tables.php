<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pivot tables first (children before parents)
        Schema::dropIfExists('pack_definition_pack_operation');
        Schema::dropIfExists('pack_definition_pack_group');

        // 2. Final invoices (standalone, no FK dependencies after parent tables created)
        Schema::dropIfExists('final_invoice_items');
        Schema::dropIfExists('final_invoices');

        // 3. New item packs (stub table, no FK dependencies)
        Schema::dropIfExists('new_item_packs');

        // 4. Pack definitions last (parent table)
        Schema::dropIfExists('pack_definitions');
    }

    public function down(): void
    {
        // No rollback for obsolete tables
    }
};
