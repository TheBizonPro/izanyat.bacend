<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKtirTypeToPlatformInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('platform_infos', function (Blueprint $table) {
            $table->string('ktir_type', 20)
                ->default('ktir-2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('platform_infos', function (Blueprint $table) {
            $table->dropColumn('ktir_type');
        });
    }
}
