<?php

namespace App\Console\Commands\FNS;

use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Console\Command;
use App\Jobs\FNS\CheckTaxpayerBindStatus as FNSCheckTaxpayerBindStatus;
use App\Models\User;
use App\Jobs\FNS\UpdateTaxpayerData as FNSUpdateTaxpayerData;

class CheckBoundTaxpayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fns:check_bound_taxpayers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка подключенных пользователей на предмет';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(LogsService $logsService)
    {
        //TODO переписать на сервис
        dump('Проверка привязки пользователей к платформе в ПП НПД ФНС.');

        $users = User::where('is_selfemployed', '=', true)
            ->where(function($query){
                $query
                    ->whereNull('taxpayer_binded_to_platform')
                    ->orWhere('taxpayer_binded_to_platform', '=', false);
            })
            ->whereNotNull('taxpayer_bind_id')
            ->get();


        dump('Пользователей, ожидающих проверку: ' . $users->count());


        foreach ($users as $user) {
            dump('- Проверка пользователя ИНН' . $user->inn . ', ' . $user->name);
            $status = null;
            try {
                $status = FNSCheckTaxpayerBindStatus::dispatchNow($user->taxpayer_bind_id);
                dump('-- cтатус: ' . $status);
            } catch (\Throwable $e) {
                dump('-- ошибка: ' . $e->getMessage());
                continue;
            }

            if ($status === 'IN_PROGRESS') {
                $logsService->userLog('Пользователь пока не подтвердил привязку к "Мой Налог"', $user->id, []);
                continue;
            }

            if ($status === 'COMPLETED') {
                $user->taxpayer_registred_as_npd = true;
                $user->taxpayer_binded_to_platform = true;
                $user->taxpayer_bind_id = null;

                $logsService->userLog('Пользователь подтвердил привязку к "Мой Налог"', $user->id, []);

                FNSUpdateTaxpayerData::dispatch($user);

                /**
                 * Подгрузка уведомлений
                 */
//                try{
//                    FNSGetNotifications::dispatch($user);
//                } catch (\Throwable $e) {}

            } else if ($status === 'FAILED') {
                $user->taxpayer_registred_as_npd = null;
                $user->taxpayer_binded_to_platform = false;
                $user->taxpayer_bind_id = null;

                $logsService->userLog('Пользователь отменил привязку в "Мой Налог"', $user->id, []);

            }

            $user->update();
        }

        dump('Конец работы скрипта');
        dump('');
    }
}
