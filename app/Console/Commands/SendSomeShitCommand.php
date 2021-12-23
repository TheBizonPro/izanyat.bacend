<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\MobiService;
use Illuminate\Console\Command;

class SendSomeShitCommand extends Command
{
    protected $signature = 'test:shit';

    protected $description = 'Command description';

    /**
     * @throws \SoapFault
     * @throws \Exception
     */
    public function handle()
    {
            $client = new MobiService(Company::findOrFail(2));
            $resp = $client->getBalance();
            dump($resp);

        //    $masterToken = env('FNS_MASTER_TOKEN');
        //    $userToken = 1;
        //    $ktirUrl = config('npd.ktir_url');
        //    $FNSClient = new Client($masterToken, $userToken, $ktirUrl);
        //    $FNSClient->setStoringTempToken(true);
        //    $FNSClient->setCacheDir(storage_path('app/cache'));
        //    $FNSClient->authIfNecessary(config('npd.auth_url'));
//
//        dump('FNS getTempToken ' . $FNSClient->getTempToken());
//        dump('FNS getTempTokenExpireTime ' . $FNSClient->getTempTokenExpireTime());
//
//        $Api = new FnsSmzApi($FNSClient);
//        $answer = $Api->getActivitiesListRequestV2();
//
//        dump($answer);
//        dump($resp);
        // $masterToken = env('FNS_MASTER_TOKEN');
        // $userToken = "1";

        // $ktirUrl = config('npd.ktir_url');
        // $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);

        // $FnsSmzClient->setStoringTempToken(true);
        // $FnsSmzClient->setCacheDir(storage_path('app/cache'));
        // $FnsSmzClient->authIfNecessary(config('npd.auth_url'));

        // $FnsSmzApi = new FnsSmzApi($FnsSmzClient);

        // $response = $FnsSmzApi->getKeysRequest($this->userInns);
        // dump($response);
//        UpdateMobiFLRegistrationStatusJob::dispatch(User::firstOrFail());
//        $masterToken = env('FNS_MASTER_TOKEN');
//        $userToken = "1";
//        $ktirUrl = config('npd.ktir_url');
//        $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);
//        $FnsSmzClient->setStoringTempToken(true);
//        $FnsSmzClient->setCacheDir(storage_path('app/cache'));
//        $FnsSmzClient->authIfNecessary(config('npd.auth_url'));
        //$FnsSmzClient->setDebug(true);
        //$FnsSmzClient->setLogFile(storage_path('app/public/lastfiscal.log'));
//        $FnsSmzApi = new FnsSmzApi($FnsSmzClient);
//        $payout =null;
        /**
         * Формируем запрос на фискализацию входящих денег
         */
//        $Inn = '578582583922';
//        $RequestTime = Carbon::now()->toAtomString();
//        $OperationTime = Carbon::now()->toAtomString();
//        $IncomeType = 'FROM_LEGAL_ENTITY';
//        $CustomerInn = '737143827400';
//        $CustomerOrganization = 'ООО "РИК"';
//        $Service = [];
//        $Service['Amount'] = '1000';
//        $Service['Name'] = 'Консультация';
//        $Service['Quantity'] = 1;
//        $Services = [$Service];
//        $TotalAmount = 1000;
//        $ReceiptId = $payout ?: null;
//        $Link = $payout ?: null;
//        $IncomeHashCode = $payout ?: null;

        /**
         * Отправляем запрос
         */

//        $answer = $FnsSmzApi->postIncomeRequestV2(
//            $Inn,
//            $ReceiptId,
//            $RequestTime,
//            $OperationTime,
//            $IncomeType,
//            $CustomerInn,
//            $CustomerOrganization,
//            null,
//            $Services,
//            $TotalAmount,
//            $IncomeHashCode,
//            $Link,
//            config('fns.test_offline_mode')
//        );
//        AnnulateReceipt::dispatchNow();
//        dump($answer);
    }
}
