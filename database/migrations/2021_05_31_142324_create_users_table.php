<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('email')->nullable()->default(null)->unique();
			$table->bigInteger('phone')->nullable()->default(null)->unique();
			$table->string('phone_code')->nullable()->default(null);
			$table->bigInteger('phone_code_sent_at')->nullable()->default(null);
			$table->boolean('phone_confirmed')->default(false);
			$table->string('password')->nullable()->default(null);
			$table->rememberToken();
			$table->string('inn')->nullable()->default(null)->unique();
			$table->string('firstname')->nullable()->default(null);
			$table->string('lastname')->nullable()->default(null);
			$table->string('patronymic')->nullable()->default(null);
			$table->enum('sex', ['m', 'f'])->nullable()->default(null);
			$table->string('birth_place')->nullable()->default(null);
			$table->date('birth_date')->nullable()->default(null);
			$table->string('passport_series')->nullable()->default(null);
			$table->string('passport_number')->nullable()->default(null);
			$table->string('passport_code')->nullable()->default(null);
			$table->string('passport_issuer')->nullable()->default(null);
			$table->date('passport_issue_date')->nullable()->default(null);
			$table->string('snils')->nullable()->default(null);

			$table->boolean('is_identified')->nullable()->default(false);
			$table->bigInteger('signme_id')->nullable()->default(null);
			$table->string('signme_code')->nullable()->default(null);

			$table->boolean('taxpayer_registred_as_npd')->nullable()->default(null);
			$table->boolean('taxpayer_binded_to_platform')->nullable()->default(null);
			$table->boolean('taxpayer_income_limit_not_exceeded')->nullable()->default(null);
			$table->bigInteger('taxpayer_bind_id')->nullable()->default(null);

			$table->double('rating', 3, 2)->nullable()->default(null);
			$table->text('about')->nullable()->default(null);

			$table->boolean('is_administrator')->default(false);
			$table->boolean('is_client')->default(false);
			$table->boolean('is_selfemployed')->default(false);

			$table->foreignId('job_category_id')->unsigned()->nullable()->default(null)->constrained('job_categories', 'id')->onDelete('set null');
			$table->foreignId('company_id')->nullable()->default(null)->constrained('companies', 'id')->onDelete('restrict');
			$table->string('address_region')->nullable()->default(null);
			$table->string('address_city')->nullable()->default(null);
			$table->string('address_street')->nullable()->default(null);
			$table->string('address_house')->nullable()->default(null);
			$table->string('address_building')->nullable()->default(null);
			$table->string('address_flat')->nullable()->default(null);
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
		Schema::dropIfExists('users');
	}
}
