<?php

namespace App\Logging;

use App\Services\Telegram\TelegramAdminBotClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Monolog\Handler\AbstractProcessingHandler;

class FileLoggerHandler extends AbstractProcessingHandler
{

    protected function write(array $record): void
    {
        $context = $record['context'];
        $logData = $context['data'];
        $logType = $context['type'];

        $directory = storage_path('logs/users/' . $context['user_id'] . '/' . date('d.m.Y') . '/');
        $file = storage_path('general.log');
        $timestamp = "[" . date('d.m.Y h.i:s') . "] ";

        $logDir = storage_path('logs/users/' . $context['user_id'] . '/' . date('d.m.Y'));

        if (! is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        switch ($logType) {
            case 'userlog':
                $file = $directory . 'user.log';
                break;
            case 'fnslog':
                $file = $directory . 'fns.log';
                break;
            case 'signmelog':
                $file = $directory . 'signme.log';
                break;
            case 'mobilog':
                $file = $directory . 'mobi.log';
                break;

        }


        $log = $timestamp . $record['message'] . ' ' . json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";

        File::append($file, $log);
    }
}
