<?php

namespace App\Providers;

use App\Services\LogsService;
use Illuminate\Support\ServiceProvider;

class FileSystemLogsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(LogsService::class, function () {
            return new LogsService();
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
