<?php

namespace App\Providers;

use App\Services\NotificationsService;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(NotificationsService::class, function () {
            return new NotificationsService();
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
