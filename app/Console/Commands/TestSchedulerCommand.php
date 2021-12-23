<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramAdminBotClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestSchedulerCommand extends Command
{
    protected $signature = 'scheduler:test';

    protected $description = 'Command description';

    public function handle()
    {
        TelegramAdminBotClient::sendAdminNotification('Тестовый шедулер работает, время: ' . time());
    }
}
