<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTinkoffCardFields extends Migration
{
    public function up()
    {
        Schema::table('tinkoff_contractor_cards', function (Blueprint $table) {
            $table->dropColumn('card_token');
            $table->date('card_expires')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tinkoff_contractor_cards', function (Blueprint $table) {
            $table->string('card_token')->nullable();
        });
    }
}
