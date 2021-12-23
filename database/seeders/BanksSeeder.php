<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as GuzzleHttpClient;
use Mtownsend\XmlToArray\XmlToArray;
use Illuminate\Support\Arr;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        dump('Получение XML с банками');

        $url = 'https://bik-info.ru/base/base.xml';
        dump('GET ' . $url);

        $client = new GuzzleHttpClient();
        try {
            $responseXml = $client->request('GET', $url, [
                'http_errors' => false
            ]);
        } catch (\Throwable $e) {
            throw new \Exception("Ошибка получения данных банков. " . $e->getMessage());
        }

        $responseArray = XmlToArray::convert($responseXml->getBody());

        $banks = [];
        foreach($responseArray as $i => $item) {
            foreach($item as $n => $bank) {
                $bank = Arr::get($bank, '@attributes');
                if (Arr::get($bank, 'bik') == null OR Arr::get($bank, 'name') == null) {
                    continue;
                }

                $banks[]= [
                    'name' => Arr::get($bank, 'name'),
                    'bik' => Arr::get($bank, 'bik'),
                    'ks' => Arr::get($bank, 'ks'),
                    'index' => Arr::get($bank, 'index'),
                    'city' => Arr::get($bank, 'city'),
                    'address' => Arr::get($bank, 'address'),
                    'okato' => Arr::get($bank, 'okato'),
                    'okpo' => Arr::get($bank, 'okpo'),
                    'regnum' => Arr::get($bank, 'regnum'),
                    'dateadd' => Arr::get($bank, 'dateadd')
                ];
            }
        }

        dump('Получено записей о банках ' . count($banks));

        if (count($banks) > 50) {
            dump('Очистка таблицы banks');
            DB::table('banks')->truncate();
            dump('Запись полученных данных в banks');
            DB::table('banks')->insert($banks);
            dump('Готово');
        }

    }
}
