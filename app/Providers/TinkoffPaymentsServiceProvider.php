<?php

namespace App\Providers;

use App\Logging\LogsHelper;
use App\Services\TinkoffService;
use Illuminate\Support\ServiceProvider;

class TinkoffPaymentsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TinkoffService::class, fn() => new TinkoffService(
            \Request::user(),
            LogsHelper::createFsLogger()
        ));
    }

    public function boot()
    {
        //
    }
}
