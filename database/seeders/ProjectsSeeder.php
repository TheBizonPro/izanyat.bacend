<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;


class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$projects = [
    		["company_id" => 1, "name" => "Бургер кинг", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "Пятерочка", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "КФС", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "Сабвэй", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "Барклай плаза", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "Пушкин", "created_at" => Carbon::today()->subDays(rand(0, 365))],
    		["company_id" => 1, "name" => "Ресо офис", "created_at" => Carbon::today()->subDays(rand(0, 365))]
    	];

    	//DB::table('projects')->insert($projects);
    }
}
