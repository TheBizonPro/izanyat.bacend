<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->enum('type', ['contract', 'work_order', 'act', 'reciept', 'other', 'signme_anketa', 'agreement']);
			$table->foreignId('project_id')->nullable()->default(null)->constrained('projects', 'id');
			$table->foreignId('user_id')->constrained('users', 'id');
			$table->foreignId('task_id')->nullable()->default(null)->constrained('tasks', 'id');
			$table->foreignId('payout_id')->nullable()->default(null)->constrained('payouts', 'id');
			$table->string('number')->nullable()->default(null);
			$table->date('date');
			$table->string('file')->nullable()->default(null);
			$table->string('hash')->nullable()->default(null);
			$table->boolean('company_sign_requested')->default(false);
			$table->boolean('user_sign_requested')->default(false);
			$table->string('company_sig')->nullable()->default(null);
			$table->string('user_sig')->nullable()->default(null);
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
		Schema::dropIfExists('documents');
	}
}
