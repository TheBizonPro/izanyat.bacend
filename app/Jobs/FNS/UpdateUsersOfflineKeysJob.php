<?php

namespace App\Jobs\FNS;

use App\Models\User;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UpdateUsersOfflineKeysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $maxExpireDate = now()->addDay();

        $query = User::query()
            ->where([
                ['taxpayer_registred_as_npd', true],
                ['taxpayer_binded_to_platform', true]
            ]);

        $query->whereDoesntHave('npdOfflineKeys', function (Builder $query) use ($maxExpireDate) {
            $query->whereDate('expire_time', '<=', $maxExpireDate);
        });

        $userInns = $query->get()->pluck('inn')->toArray();

        GetOfflineKeys::dispatchSync($userInns);
    }
}
