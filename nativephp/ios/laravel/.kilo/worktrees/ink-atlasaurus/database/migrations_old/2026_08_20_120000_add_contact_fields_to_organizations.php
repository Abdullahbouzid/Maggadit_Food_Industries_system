<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('gln');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('email')->nullable()->after('whatsapp');
            $table->string('website')->nullable()->after('email');
            $table->string('address')->nullable()->after('website');
            $table->boolean('forward_contact_messages_to_email')->default(false)->after('address');
            $table->string('contact_email_recipient')->nullable()->after('forward_contact_messages_to_email');
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'whatsapp',
                'email',
                'website',
                'address',
                'forward_contact_messages_to_email',
                'contact_email_recipient',
            ]);
        });
    }
};
