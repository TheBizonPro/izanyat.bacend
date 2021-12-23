<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersCompaniesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_companies', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
			$table->foreignId('company_id')->constrained('companies', 'id')->onDelete('cascade');
			$table->timestamps();
			$table->unique(['user_id', 'company_id']);
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
		Schema::dropIfExists('users_companies');
	}
}
