<?php

namespace App\Providers;

use App\Services\TasksService;
use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(TasksService::class, function () {
            return new TasksService();
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
