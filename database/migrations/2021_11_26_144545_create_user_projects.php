<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProjects extends Migration
{
    public function up()
    {
        Schema::create('user_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id');
            $table->foreignId('project_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_projects');
    }
}
