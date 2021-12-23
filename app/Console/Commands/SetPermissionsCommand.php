<?php

namespace App\Console\Commands;

use App\Services\PermissionsHelper;
use Illuminate\Console\Command;

class SetPermissionsCommand extends Command
{
    protected $signature = 'permissions:set';

    protected $description = 'Set list of default global permissions';

    public function handle()
    {
        PermissionsHelper::setGlobalPermissionsList();
    }
}
