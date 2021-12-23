<?php

namespace App\Jobs;

use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Queue\Events\JobProcessed;

class JobsEventHandlers
{
    public static function getBeforeDispatchEventHandlers(): array
    {
        return [
            'App\Jobs\UpdateUserReceiptsFromFNS' => function (JobProcessed $job) {
                // some logic
            },


        ];
    }
}
