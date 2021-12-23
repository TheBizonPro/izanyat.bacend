<?php

namespace App\Console\Commands;

use App\Jobs\UpdateUserReceiptsFromFNS;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateUsersReceiptsStatusesFromFNS extends Command
{
    protected $signature = 'receipt:update_all';

    protected $description = 'Обновление данных по всем чекам';

    public function handle()
    {
        User::whereNull('company_id')->whereNotNull('inn')->whereTaxpayerBindedToPlatform(1)->get()->each(function (User $user) {
            UpdateUserReceiptsFromFNS::dispatch($user);
            dump('Пользователь ' . $user->id . ' поставлен в очередь на проверку чеков');
        });
    }
}
