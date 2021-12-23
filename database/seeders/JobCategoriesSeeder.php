<?php

namespace Database\Seeders;

use App\Services\Fns\FNSService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\JobCategory;


use VoltSoft\FnsSmz\FnsSmzClient as Client;
use VoltSoft\FnsSmz\FnsSmzApi;



class JobCategoriesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(FNSService $fnsService)
	{
		dump('Job categories');
		dump('Env = ' . config('app.env'));

		if (config('app.env') == 'local') {


			$job_categories = [
				'Курьерские услуги',
				'Уборки, услуги клининга',
				'Мерчендайзинг',
				'Ремонт и строительство',
				'Продажи и маркетинг',
				'Дизайн',
				'Услуги личного помощника',
				'Разработка ПО',
				'Фото, видео, аудио',
				'Установка и ремонт техники',
				'Красота и здоровье',
				'Грузоперевозки',
				'Обучение, тренинги, репетиторы',
				'Ремонт трансорта',
				'Юридические и бухгалтерские услуги'
			];


			$parent_category = new JobCategory;
			$parent_category->parent_id = null;
			$parent_category->name = 'Прочее';
			$parent_category->sort = 0;
			$parent_category->save();
			$parent_category->refresh();


			foreach ($job_categories as $key => $name) {
				DB::table('job_categories')->insert([
					'name' => $name,
					'parent_id' => $parent_category->id,
					'sort' => $key
				]);
				dump($name . ' inserted!');
			}
		} else if (config('app.env') == 'production') {


			$answer = $fnsService->categories();

			dump($answer);

			if (Arr::exists($answer, 'Activities')) {

				foreach ($answer['Activities'] as $Activity) {

					$id = Arr::get($Activity, 'Id');
					$name = Arr::get($Activity, 'Name');
					$is_active = Arr::get($Activity, 'IsActive');
					$parent_id = Arr::get($Activity, 'ParentId');

					if ($is_active === "false" ) {
						$is_active = false;
					} else {
						$is_active = true;
					}

					if (isset($id) == false OR isset($name) == false) {
						continue;
					}

					$JobCategory = JobCategory::updateOrCreate(
						['id' => $id],
						['name' => $name, 'parent_id' => $parent_id, 'is_active' => $is_active]
					);

					dump('Updated ' . $name . ' parent=' . $parent_id . ' active=' . $is_active);
				}


			}

		}








	}
}
