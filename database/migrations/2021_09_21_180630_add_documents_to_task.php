<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentsToTask extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('act_id')->nullable()->references('id')->on('documents');
            $table->foreignId('order_id')->nullable()->references('id')->on('documents');
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('act_id');
            $table->dropColumn('order_id');
        });
    }
}
