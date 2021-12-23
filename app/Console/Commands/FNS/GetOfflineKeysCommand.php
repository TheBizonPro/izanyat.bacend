<?php

namespace App\Console\Commands\FNS;

use App\Jobs\FNS\UpdateUsersOfflineKeysJob;
use App\Models\User;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class GetOfflineKeysCommand extends Command
{
    protected $signature = 'fns:get_offline_keys';

    protected $description = 'Получает оффлайн ключи для всех самозанятых, у которых менее 50 ключей';

    public function handle()
    {
        UpdateUsersOfflineKeysJob::dispatchSync();
    }
}
