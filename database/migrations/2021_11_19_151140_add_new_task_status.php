<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewTaskStatus extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            DB::statement("ALTER TABLE tasks CHANGE COLUMN status status ENUM('new', 'work', 'done', 'await_payment', 'await_payment_request', 'await_money', 'paid') NOT NULL DEFAULT 'new'");
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            DB::statement("ALTER TABLE tasks CHANGE COLUMN status status ENUM('new', 'work', 'done', 'await_payment', 'await_money', 'paid') NOT NULL DEFAULT 'new'");
            // $table->enum('status', ['new', 'work', 'done', 'await_payment', 'paid'])->default('new');
        });
    }
}
