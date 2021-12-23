<?php

namespace App\Console\Commands\deployment;

use App\Models\PlatformInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DeployInitCommand extends Command
{
    protected $signature = 'deploy:init';

    protected $description = 'Command description';

    public function handle()
    {
        if (! file_exists('/app/.env')) {
            throw new \Exception('.env file does not exist');
        }


        if (! Schema::hasTable('migrations')) {
            dump('Migrations not executed, running with seeder');
            \Artisan::call('migrate --force --seed');
        } else {
            dump('Migrations founded, executing without seeder');
            \Artisan::call('migrate --force');
        }

        $fnsPlatformInfo = PlatformInfo::first();

        if (! $fnsPlatformInfo) {
            dump('FNS platform info not found, running fns:platform_registration command');
            \Artisan::call('fns:platform_registration');
        } else {
            dump('FNS platform registered');
        }

        $JWT = env('JWT_SECRET');

        if (! $JWT) {
            dump('JWT not founded, running jwt:secret command');
            \Artisan::call('jwt:secret');
        } else {
            dump('JWT founded');
        }

        dump('app ready!');
    }
}
