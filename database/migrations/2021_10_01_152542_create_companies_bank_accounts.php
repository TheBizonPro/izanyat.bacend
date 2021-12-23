<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesBankAccounts extends Migration
{
    public function up()
    {
        Schema::create('companies_bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobi_partner_id')->nullable();
            $table->string('mobi_secret_password')->nullable();
            $table->boolean('mobi_connected')->default(0);
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
    }

    public function down()
    {
//        Schema::dropIfExists('companies_bank_accounts');
    }
}
