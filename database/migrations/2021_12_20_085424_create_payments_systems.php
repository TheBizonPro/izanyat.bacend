<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsSystems extends Migration
{
    public function up()
    {
        Schema::create('payments_systems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('display_name');
            $table->string('code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments_systems');
    }
}
