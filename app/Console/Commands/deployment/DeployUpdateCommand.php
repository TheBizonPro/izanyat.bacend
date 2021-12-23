<?php

namespace App\Console\Commands\deployment;

use App\Models\PlatformInfo;
use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Schema;

class DeployUpdateCommand extends Command
{
    protected $signature = 'deploy:update';

    protected $description = 'Command description';

    public function handle()
    {

    }
}
