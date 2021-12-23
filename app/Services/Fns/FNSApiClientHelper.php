<?php

namespace App\Services\Fns;

use VoltSoft\FnsSmz\FnsSmzApi;
use VoltSoft\FnsSmz\FnsSmzClient;

class FNSApiClientHelper
{
    public static function getApiClient()
    {
        $masterToken = env('FNS_MASTER_TOKEN');
        $userToken = "1";
        $ktirUrl = config('npd.ktir_url');
        $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);
        $FnsSmzClient->setStoringTempToken(true);
        $FnsSmzClient->setCacheDir(storage_path('app/cache'));
        $FnsSmzClient->authIfNecessary(config('npd.auth_url'));
        return new FnsSmzApi($FnsSmzClient);

    }
}
