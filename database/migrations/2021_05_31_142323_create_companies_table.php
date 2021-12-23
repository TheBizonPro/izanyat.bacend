<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('signer_user_id')->unsigned()->nullable()->default(null);
			$table->double('balance', 15, 2)->default(0);
			$table->string('name');
			$table->string('full_name')->nullable()->default(null);
			$table->string('address_region')->nullable()->default(null);
			$table->string('address_city')->nullable()->default(null);
			$table->string('legal_address')->nullable()->default(null);
			$table->string('fact_address')->nullable()->default(null);
			$table->string('inn')->nullable()->default(null)->unique();
			$table->string('ogrn')->nullable()->default(null)->unique();
			$table->string('okpo')->nullable()->default(null);
			$table->string('kpp')->nullable()->default(null);
			$table->string('email')->nullable()->default(null)->unique();
			$table->text('about')->nullable()->default(null);
			$table->bigInteger('phone')->nullable()->default(null)->unique();
			$table->bigInteger('signme_id')->nullable()->default(null)->unique();
			$table->timestamps();
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
		Schema::dropIfExists('companies');
	}
}
