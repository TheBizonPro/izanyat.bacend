<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('fns_id')->nullable()->default(null);
			$table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
			$table->boolean('is_readed')->default(false);
			$table->string('from');
			$table->string('subject');
			$table->text('text');
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
		Schema::dropIfExists('notifications');
	}
}
