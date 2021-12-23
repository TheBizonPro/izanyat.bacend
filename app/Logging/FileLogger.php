<?php

namespace App\Logging;

use Monolog\Logger;

class FileLogger
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger("FileLoggerHandler");
        return $logger->pushHandler(new FileLoggerHandler());
    }
}
