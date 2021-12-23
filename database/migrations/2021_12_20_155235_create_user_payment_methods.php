<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPaymentMethods extends Migration
{
    public function up()
    {
        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class);
            $table->string('payment_type_code');
            $table->integer('payment_method_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_payment_methods');
    }
}
