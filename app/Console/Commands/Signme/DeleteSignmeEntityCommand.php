<?php

namespace App\Console\Commands\Signme;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use PackFactory\SignMe\SignMe;

class DeleteSignmeEntityCommand extends Command
{
    protected $signature = 'signme:delete {inn}';

    protected $description = 'Удаление сущности из signme по ИНН';

    public function handle()
    {
        $inn = $this->argument('inn');

        $user = User::whereInn($inn)->first();
        $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));

        $signMeId = false;
        $signMeState = $user->signMeState ?? throw new \Exception('Сайнми стейт не найден');

        if ($user) {
            dump('Пользователь найден');

            $signMeId = $signMeState->signme_id;

            dump('Шлем запрос на удаление, id: ' . $signMeId);
            $response = $signMe->deleteByRequestId($signMeId);
            dump($response);
        }



        $company = Company::whereInn($inn)->first();

        if ($company) {
            dump('Компания найдена');

            if (!isset($company->signme_id)) {
                dump('У компании нет signme_id');
                dump('Конец скрипта');
                exit;
            }

            $signMeId = $company->signme_id;
            dump('Шлем запрос на удаление компании, signme_id:' . $signMeId);
            $response = $signMe->deleteByRequestId($signMeId);
            dump($response);


            $signMeId = $company->signme_id;
            dump('Шлем запрос на удаление компании, signme_id:' . $signMeId);
            $response = $signMe->deleteByRequestId($signMeId);
            dump($response);

            if (!isset($company->signerUser->signMeState->signme_id)) {
                dump('У пользователя компании signme_id');
                dump('Конец скрипта');
                exit;
            }

            $signMeId = $company->signerUser->signMeState->signme_id;
            dump('Шлем запрос на удаление компании, signme_id:' . $signMeId);
            $response = $signMe->deleteByRequestId($signMeId);
            dump($response);
        }

        dump('Конец скрипта');
    }
}
