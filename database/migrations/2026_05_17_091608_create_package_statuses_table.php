<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('package_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status_name');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->boolean('resellable')->default(false);
            $table->boolean('expired')->default(false);
            $table->boolean('default')->default(false);
            $table->boolean('lpo_required')->default(false);
            $table->boolean('is_related_to_cn_request')->default(false);
            $table->boolean('is_price_editable')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'status_name']); // منع تكرار نفس الاسم لنفس المؤسسة
        });
    }

    public function down()
    {
        Schema::dropIfExists('package_statuses');
    }
};