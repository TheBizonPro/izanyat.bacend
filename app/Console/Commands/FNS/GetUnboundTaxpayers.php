<?php

namespace App\Console\Commands\FNS;

use App\Services\Fns\FNSService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;
use Carbon\Carbon;
use App\Models\User;
use App\Jobs\Notification\FNSUnbinded as FNSUnbindedNotification;


class GetUnboundTaxpayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fns:get_unbound_taxpayers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка пользователей отвязанных от платформы';

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
    public function handle(FNSService $fnsService)
    {
        dump('Получение списка отвязанных налогоплательщиков');

        //$From = Carbon::now()->subHours(2)->toDateTimeString();
        $From = Carbon::now(new \DateTimeZone('UTC'))->subMinutes(1)->format('Y-m-d\TH:i:s');
        $To = Carbon::now(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s');
        $Limit = 1000;
        $Offset = 0;

        dump('From = ' . $From);
        dump('To = ' . $To);

//        $masterToken = env('FNS_MASTER_TOKEN');
//        $userToken = "1";
//        $ktirUrl = config('npd.ktir_url');
//        $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);
//        $FnsSmzClient->setStoringTempToken(true);
//        $FnsSmzClient->setCacheDir(storage_path('app/cache'));
//        $FnsSmzClient->authIfNecessary(config('npd.auth_url'));
//        $FnsSmzApi = new FnsSmzApi($FnsSmzClient);
//
//        dump("Запрос...");
        $answer = $fnsService->newlyUnboundTaxpayer($From, $To, $Limit, $Offset);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }
        dump("Ответ:");
        dump($answer);

        dump("Отключение отвязанных пользователей");
        if (Arr::exists($answer, 'Taxpayers')) {

            if (Arr::get($answer, 'Taxpayers.Inn') != null) {
                $Taxpayers = [];
                $Taxpayers[]= $answer['Taxpayers'];
            } else {
                $Taxpayers = $answer['Taxpayers'];
            }

            foreach ($Taxpayers as $Taxpayer) {
                if ($Taxpayer['Inn'] != null) {
                    dump("");
                    dump("- Ищем пользователя с ИНН " . $Taxpayer['Inn']);
                    $user = User::where('inn', '=', $Taxpayer['Inn'])->first();
                    if ($user == null) {
                        dump("-- не найден");
                        continue;
                    }

                    dump("-- найден: " . $user->name);
                    dump("-- устанавливаем taxpayer_registred_as_npd = NULL ");
                    dump("-- устанавливаем taxpayer_binded_to_platform = FALSE ");
                    dump("-- устанавливаем taxpayer_income_limit_not_exceeded = NULL ");
                    $user->taxpayer_registred_as_npd = null;
                    $user->taxpayer_binded_to_platform = false;
                    $user->taxpayer_income_limit_not_exceeded = null;
                    $user->save();

                    try {
                        FNSUnbindedNotification::dispatchNow($user);
                    } catch (\Throwable $e) {

                    }

                    dump("-- готово!");
                }
            }
        }
    }
}
