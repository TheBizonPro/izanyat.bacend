<?php

namespace App\Providers;

use App\Services\Documents\DocumentsService;
use Illuminate\Support\ServiceProvider;

class DocumentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(DocumentsService::class, function () {
            return new DocumentsService();
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
