<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobCategoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('job_categories', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('parent_id')->unsigned()->nullable()->default(null);
			$table->string('name');
			$table->boolean('is_active')->default(true);
			$table->integer('sort')->default(0);
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
		Schema::dropIfExists('job_categories');
	}
}
