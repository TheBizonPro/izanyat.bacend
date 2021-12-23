<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Models\JobCategory;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		dump('Users');

		$users = [];

		$u = [];
		$u['email'] = 'user_main@gmail.com';
		$u['phone'] = 79999999999;
		$u['password'] = Hash::make('123456');
		//$u['inn'] = '772850189541';
		$u['inn'] = '957845085059';
		$u['firstname'] = 'Клиент';
		$u['lastname'] = 'Главновский';
		$u['patronymic'] = 'Петрович';
		$u['sex'] = 'm';
		$u['birth_date'] = '1989-06-07';
		$u['birth_place'] = 'г. Москва';
		$u['passport_series'] = '4510';
		$u['passport_number'] = '252006';
		$u['passport_code'] = '770-117';
		$u['passport_issuer'] = 'ОТДЕЛЕНИЕМ ПО РАЙОНУ КОНЬКОВО ОУФМС РОССИИ ПО ГОР.МОСКВЕ В ЮЗАО';
		$u['passport_issue_date'] = '2009-07-02';
		//$u['snils'] = '137-241-792 58';
		$u['snils'] = '137-241-792 58';
		$u['taxpayer_registred_as_npd'] = null;
		$u['taxpayer_binded_to_platform'] = false;
		$u['taxpayer_income_limit_not_exceeded'] = null;
		$u['is_administrator'] = false;
		$u['is_client'] = true;
		$u['is_selfemployed'] = false;
		$u['job_category_id'] = null;
		$u['company_id'] = 1;

		$u['address_region'] = 'г. Москва';
		$u['address_city'] = 'Москва';
		$u['address_street'] = 'Бутлерова';
		$u['address_house'] = 4;
		$u['address_building'] = 1;
		$u['address_flat'] = 177;
		$u['created_at'] = Carbon::now();
		$u['updated_at'] = Carbon::now();

		$users[]= $u;
		DB::table('users')->insert($users);



//		$users = [];
//
//		$u = [];
//		$u['email'] = 'andrey.surzhikov@gmail.com';
//		$u['phone'] = 79295127640;
//		$u['password'] = Hash::make('123456');
//		$u['inn'] = '772850189541';
//		$u['firstname'] = 'Андрей';
//		$u['lastname'] = 'Суржиков';
//		$u['patronymic'] = 'Владимирович';
//		$u['sex'] = 'm';
//		$u['birth_date'] = '1989-06-07';
//		$u['birth_place'] = 'г. Москва';
//		$u['passport_series'] = '4510';
//		$u['passport_number'] = '252006';
//		$u['passport_code'] = '770-117';
//		$u['passport_issuer'] = 'ОТДЕЛЕНИЕМ ПО РАЙОНУ КОНЬКОВО ОУФМС РОССИИ ПО ГОР.МОСКВЕ В ЮЗАО';
//		$u['passport_issue_date'] = '2009-07-02';
//		//$u['snils'] = '137-241-792 58';
//		$u['snils'] = '137-241-792 58';
//		$u['taxpayer_registred_as_npd'] = null;
//		$u['taxpayer_binded_to_platform'] = true;
//		$u['taxpayer_income_limit_not_exceeded'] = null;
//		$u['is_administrator'] = false;
//		$u['is_client'] = false;
//		$u['is_selfemployed'] = true;
//		$u['job_category_id'] = null;
//		$u['company_id'] = 1;
//
//		$u['address_region'] = 'г. Москва';
//		$u['address_city'] = 'Москва';
//		$u['address_street'] = 'Бутлерова';
//		$u['address_house'] = 4;
//		$u['address_building'] = 1;
//		$u['address_flat'] = 177;
//		$u['created_at'] = Carbon::now();
//		$u['updated_at'] = Carbon::now();
//
//		$users[]= $u;
//		DB::table('users')->insert($users);

//        $users = [];
//
//        $u = [];
//        $u['email'] = 'ew.mwriter@gmail.com';
//        $u['phone'] = 77999999999;
//        $u['password'] = Hash::make('123456');
//        $u['inn'] = '578582583922';
//        $u['firstname'] = 'Рич';
//        $u['lastname'] = 'Боржественный';
//        $u['patronymic'] = 'Шинцевич';
//        $u['sex'] = 'm';
//        $u['birth_date'] = '1948-11-21';
//        $u['birth_place'] = 'г. Москва';
//        $u['passport_series'] = '2322';
//        $u['passport_number'] = '649113';
//        $u['passport_code'] = '770-117';
//        $u['passport_issuer'] = 'ОУФМС РОССИИ';
//        $u['passport_issue_date'] = '1973-11-21';
//        //$u['snils'] = '137-241-792 58';
//        $u['snils'] = '927-481-823 43';
//        $u['taxpayer_registred_as_npd'] = null;
//        $u['taxpayer_binded_to_platform'] = false;
//        $u['taxpayer_income_limit_not_exceeded'] = null;
//        $u['is_administrator'] = false;
//        $u['is_client'] = false;
//        $u['is_selfemployed'] = true;
//        $u['job_category_id'] = null;
//        $u['company_id'] = null;
//
//        $u['address_region'] = 'г. Москва';
//        $u['address_city'] = 'Москва';
//        $u['address_street'] = 'Бутлерова';
//        $u['address_house'] = 4;
//        $u['address_building'] = 1;
//        $u['address_flat'] = 177;
//        $u['created_at'] = Carbon::now();
//        $u['updated_at'] = Carbon::now();
//
//        $users[]= $u;
//        DB::table('users')->insert($users);



		$users = [];
		$job_categories_ids = JobCategory::all()->pluck('id')->toArray();

//		for ($i=0; $i < 50; $i++) {
//
//			$faker = \Faker\Factory::create('ru_RU');
//			$u['email'] = $faker->unique()->email();
//			$u['phone'] = \App\Models\User::formatPhone($faker->numberBetween(79011111111, 79999999999));
//			$u['password'] = Hash::make($faker->password());
//			$u['inn'] = $faker->numberBetween(770000000000, 779999999999);
//			$u['firstname'] = $faker->firstName();
//			$u['lastname'] = $faker->lastName();
//			$u['patronymic'] = 'Иванович';
//			$u['sex'] = 'm';
//			$u['birth_date'] = $faker->date('Y-m-d');
//			$u['birth_place'] = $faker->city();
//			$u['passport_series'] = $faker->numberBetween(4100, 9999);
//			$u['passport_number'] = $faker->numberBetween(120000, 999999);
//			$u['passport_code'] = $faker->numberBetween(100, 999) . '-' . $faker->numberBetween(100, 999);
//			$u['passport_issuer'] = 'ГЛАВНЫМ ОТДЕЛЕНИЕМ ПО ВЫДАЧЕ ПАСПОРТОВ В РОССИИ ПО КРЕМЛЕВСКОМУ РАЙОНУ ОУФМС';
//			$u['passport_issue_date'] = $faker->date('Y-m-d');
//			$u['snils'] = $faker->numberBetween(111, 999) . '-' . $faker->numberBetween(111, 999) . '-' . $faker->numberBetween(111, 999) . ' ' . $faker->numberBetween(11, 99);
//			$u['taxpayer_registred_as_npd'] = false;
//			$u['taxpayer_binded_to_platform'] = false;
//			$u['taxpayer_income_limit_not_exceeded'] = false;
//			$u['is_administrator'] = false;
//			$u['is_client'] = false;
//			$u['is_selfemployed'] = true;
//			$u['job_category_id'] = Arr::random($job_categories_ids);
//			$u['company_id'] = null;
//
//			$u['address_region'] = 'Москва Город';
//			$u['address_city'] = $faker->city();
//			$u['address_street'] = $faker->streetName();
//			$u['address_house'] =  $faker->numberBetween(1, 200);
//			$u['address_building'] = $faker->numberBetween(1, 5);;
//			$u['address_flat'] =  $faker->numberBetween(1, 999);;
//
//			$u['created_at'] = Carbon::now();
//			$u['updated_at'] = Carbon::now();
//
//			dump ($u);
//			$users[]= $u;
//
//		}

		DB::table('users')->insert($users);


    }
}
