<?php

namespace App\Providers;

use App\Logging\LogsHelper;
use App\Services\SignMeService;
use Illuminate\Support\ServiceProvider;
use PackFactory\SignMe\SignMe;

class SignMeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(SignMeService::class, function () {
            $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null);

            return new SignMeService($signMe);
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
