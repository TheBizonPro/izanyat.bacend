<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptUuidToPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('receipt_uuid',20)
                ->nullable()
                ->default(null)
                ->after('receipt_url');
                
            $table->string('receipt_qr')
                ->nullable()
                ->default(null)
                ->after('receipt_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('receipt_uuid');
            $table->dropColumn('receipt_qr');
        });
    }
}
