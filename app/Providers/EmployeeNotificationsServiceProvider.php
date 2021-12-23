<?php

namespace App\Providers;

use Carbon\Laravel\ServiceProvider;
use App\Services\EmployeeNotificationsService;

class EmployeeNotificationsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(EmployeeNotificationsService::class, function () {
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
