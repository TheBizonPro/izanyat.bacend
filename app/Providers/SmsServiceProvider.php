<?php

namespace App\Providers;

use App\Services\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(SmsService::class, function () {
            return new SmsService(
                config('smsaero.url'),
                config('smsaero.login'),
                config('smsaero.password'),
                config('smsaero.sign'),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }
}
