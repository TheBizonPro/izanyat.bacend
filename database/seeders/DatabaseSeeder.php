<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
		$this->call([
			JobCategoriesSeeder::class,
            RegionsSeeder::class,
            BanksSeeder::class,
            
			CompaniesSeeder::class,
			UsersSeeder::class,
            UsersCompaniesSeeder::class,
            ProjectsSeeder::class,
		]);
    }
}
