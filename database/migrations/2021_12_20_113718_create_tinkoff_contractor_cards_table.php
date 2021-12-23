<?php

use App\Models\TinkoffContractorAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTinkoffContractorCardsTable extends Migration
{
    public function up()
    {
        Schema::create('tinkoff_contractor_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(TinkoffContractorAccount::class);
            $table->string('card_id')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_token')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tinkoff_contractor_cards');
    }
}
