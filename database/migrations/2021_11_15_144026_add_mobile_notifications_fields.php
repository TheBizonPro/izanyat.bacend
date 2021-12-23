<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMobileNotificationsFields extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->json('action')->default(NULL)->nullable();
            $table->text('plain_text')->nullable();
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('action');
            $table->dropColumn('plain_text');
        });
    }
}
