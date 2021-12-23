<?php

namespace App\Providers;

use App\Services\Fns\FNSService;
use Illuminate\Support\ServiceProvider;

class FNSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(FNSService::class, function() {
            return new FNSService();
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
