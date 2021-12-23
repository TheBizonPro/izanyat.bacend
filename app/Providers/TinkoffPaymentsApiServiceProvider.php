<?php

namespace App\Providers;

use App\Services\TinkoffPaymentsApiService;
use Illuminate\Support\ServiceProvider;

class TinkoffPaymentsApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TinkoffPaymentsApiService::class, fn() => new TinkoffPaymentsApiService(
            config('tinkoff.base_url'),
            config('tinkoff.token'),
        ));
    }

    public function boot()
    {
        //
    }
}
