<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignMeUserState extends Migration
{
    public function up()
    {
        Schema::create('signme_user_state', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->integer('signme_id')->nullable();
            $table->integer('signme_request_id')->nullable();
            $table->enum('status', ['request_in_progress', 'await_approve', 'approved']);
            $table->string('signme_code')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('signme_user_state');
    }
}
