<?php

namespace App\Providers;

use App\Services\PayoutsService;
use Illuminate\Support\ServiceProvider;

class PayoutsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(PayoutsService::class, function () {
            return new PayoutsService();
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
