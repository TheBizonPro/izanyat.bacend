<?php

namespace App\Console\Commands\Signme;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use PackFactory\SignMe\SignMe;

class CheckByInnCommand extends Command
{
    protected $signature = 'signme:check {inn}';

    protected $description = 'Проверка статуса сущности по ИНН';

    public function handle()
    {
        $inn = $this->argument('inn');

        $user = User::whereInn($inn)->first();
        $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));

        $precheckRequest = [];

        if ($user) {
            dump('Пользователь найден');
            $precheckRequest['phone'] = $user->phone;
            $precheckRequest['snils'] = $user->forSignMe('snils');
        } else {
            dump('Пользователь с таким ИНН не найден, пробуем найти компанию');
            $company = Company::whereInn($inn)->first();

            if (!$company) {
                dump('Не найдено компании с таким ИНН');
                dump('Конец скрипта');
                exit;
            }

            $precheckRequest['cinn'] = $company->forSignMe('cinn');
        }

        dump('Получили ответ');
        $response = $signMe->precheck($precheckRequest);

        dump($response);
    }
}
