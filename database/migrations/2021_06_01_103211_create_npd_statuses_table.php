<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpdStatusesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('npd_statuses', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
			$table->boolean('taxpayer_registred_as_npd')->default(false);
			$table->boolean('taxpayer_binded_to_platform')->default(false);
			$table->boolean('taxpayer_income_limit_not_exceeded')->nullable()->default(null);
			$table->string('fail_reason_code')->nullable()->default(null);
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
		Schema::dropIfExists('npd_statuses');
	}
}
