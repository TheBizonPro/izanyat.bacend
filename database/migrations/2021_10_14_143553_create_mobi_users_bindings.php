<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobiUsersBindings extends Migration
{
    public function up()
    {
        Schema::create('mobi_users_bindings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->string('mobi_confirm_id')->nullable();
            $table->boolean('is_identified')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobi_users_bindings');
    }
}
