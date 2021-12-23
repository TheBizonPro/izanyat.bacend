<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	dump('Users companies');

    	DB::table('users_companies')->insert([
			'user_id' => 1,
			'company_id' => 1
    	]);

        DB::table('companies')->where('id', '=', 1)->update(['signer_user_id' => 1]);

    }
}
