<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tasks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('project_id')->constrained('projects', 'id')->onDelete('restrict');
			$table->string('name')->default(null);
			$table->enum('status', ['new', 'work', 'done', 'await_payment', 'paid'])->default('new');
			$table->text('description')->nullable()->default(null);
			$table->string('address')->nullable()->default(null);
			$table->foreignId('user_id')->nullable()->references('id')->on('users');
			$table->foreignId('job_category_id')->references('id')->on('job_categories');
            $table->date('date_from');
			$table->date('date_till');
			$table->double('sum', 15, 2);
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
		Schema::dropIfExists('tasks');
	}
}
