<?php

namespace App\Console\Commands\Signme;

use App\Models\Company;
use App\Models\User;
use App\Services\LogsService;
use Illuminate\Console\Command;
use PackFactory\SignMe\SignMe;

class ActivateCompanyCommand extends Command
{
    protected $signature = 'signme:activate_company {inn}';

    protected $description = 'Activate company by inn';

    public function handle(LogsService $logsService)
    {
        $inn = $this->argument('inn');

        $company = Company::whereInn($inn)->first();

        if (! $company) {
            dump('Компания с ИНН ' . $inn . ' не найдена');
            dump('Конец скрипта');
            exit;
        }

        dump('Компания найдена, id в signme:' . $company->signme_id);

        $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));
        $response = $signMe->activateCompany($company->signme_id);

        $logsService->signmeLog('Активирована компания', $company->signer_user_id, $company->toArray());

        if ($response)
            dump('Привязка выполнена успешно');
        else
            dump('Не удалось привязать компанию');

        dump('Конец скрипта');
    }
}
