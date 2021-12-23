<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->default(null);
            $table->string('bik')->nullable()->default(null);
            $table->string('ks')->nullable()->default(null);
            $table->string('index')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('address')->nullable()->default(null);
            $table->string('okato')->nullable()->default(null);
            $table->string('okpo')->nullable()->default(null);
            $table->string('regnum')->nullable()->default(null);
            $table->string('dateadd')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('banks');
    }
}
