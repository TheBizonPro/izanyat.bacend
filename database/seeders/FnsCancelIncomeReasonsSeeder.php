<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use VoltSoft\FnsSmz\FnsSmzClient as Client;
use VoltSoft\FnsSmz\FnsSmzApi;


class FnsCancelIncomeReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('app.env') == 'production') {

            dump('FNS Auth......');

            $masterToken = env('FNS_MASTER_TOKEN');
            $userToken = 1;
            $FNSClient = new Client($masterToken, $userToken);
            $FNSClient->setStoringTempToken(true);
            $FNSClient->setCacheDir(storage_path('app/cache'));
            $FNSClient->authIfNecessary(config('npd.auth_url'));

            dump('FNS getTempToken ' . $FNSClient->getTempToken());
            dump('FNS getTempTokenExpireTime ' . $FNSClient->getTempTokenExpireTime());

            $Api = new FnsSmzApi($FNSClient);
            $answer = $Api->getCancelIncomeReasonsListRequest();

            dump($answer);


        }
    }
}
