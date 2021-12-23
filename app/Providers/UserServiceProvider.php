<?php

namespace App\Providers;

use App\Services\UsersService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(UsersService::class, function() {
            return new UsersService();
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
