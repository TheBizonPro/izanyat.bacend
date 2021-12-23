<?php

namespace App\Logging;

use App\Services\LogsService;
use JetBrains\PhpStorm\Pure;

class LogsHelper
{
    #[Pure]
    public static function createFsLogger(): LogsService
    {
        return new LogsService();
    }
}
