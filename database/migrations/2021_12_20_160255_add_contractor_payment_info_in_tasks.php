<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractorPaymentInfoInTasks extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table
                ->foreignId('contractor_payment_method_id')
                ->nullable()
                ->constrained('user_payment_methods')
                ->onDelete('cascade');


            $table->string('company_payment_type')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('contractor_payment_method_id');
            $table->dropColumn('company_payment_type');
        });
    }
}
