<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTinkoffContractorAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('tinkoff_contractor_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class);
            $table->string('tinkoff_customer_key');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tinkoff_contractor_accounts');
    }
}
