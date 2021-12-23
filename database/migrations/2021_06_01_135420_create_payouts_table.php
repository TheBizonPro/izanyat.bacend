<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payouts', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('project_id')->constrained('projects', 'id')->onDelete('restrict');
			$table->foreignId('task_id')->constrained('tasks', 'id')->onDelete('cascade');
			$table->foreignId('user_id')->constrained('users', 'id')->onDelete('restrict');
			$table->foreignId('job_category_id')->constrained('job_categories', 'id')->onDelete('restrict');
			$table->double('sum', 15, 2);
			$table->enum('status', ['draft', 'process', 'complete', 'canceled', 'error']);
			$table->longText('description')->nullable()->default(null);
			$table->longText('error_description')->nullable()->default(null);
			$table->string('payment_id')->nullable()->default(null)->unique();
			$table->string('receipt_id')->nullable()->default(null);
			$table->string('receipt_url')->nullable()->default(null);
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
		Schema::dropIfExists('payouts');
	}
}
