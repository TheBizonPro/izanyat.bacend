<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		dump('Companies');

		$companies = [
			[
				'id' => 1,
				'signer_user_id' => null,
				'name' => 'ООО "СПОРТ-АЙТИ"',
				'full_name' => 'Общество с ограниченной ответственностью "СПОРТ-АЙТИ"',
				'address_region' => 'Московская Область',
				'address_city' => 'Подольск',
				'legal_address' => 'Московская обл, г Подольск, поселок Стрелковской фабрики, ул Спортивная, д 4, пом 1',
				'fact_address' => 'Московская обл, г Подольск, поселок Стрелковской фабрики, ул Спортивная, д 4, пом 1',
				'inn' => '5074067672',
				'ogrn' => '1215000001810',
				'okpo' => '29633335',
				'kpp' => '507401001',
				'email' => 'sportit@sportit.ru',
				'phone' => 74950001122
			],

		];

		DB::table('companies')->insert($companies);

    }
}
