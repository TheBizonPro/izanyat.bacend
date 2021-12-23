<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSignMeDataFromUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'signme_id'))
                $table->dropColumn('signme_id');

            if (Schema::hasColumn('users', 'signme_code'))
                $table->dropColumn('signme_code');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signme_id');
            $table->string('signme_code');
        });
    }
}
