<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;
use Psr\Http\Client\ClientExceptionInterface;

class DeleteAllSignmeEntitiesCommand extends Command
{
    protected $signature = 'signme:delete_all';

    protected $description = 'Delete all signme binds';

    public function handle()
    {
        $users = [];
        $companies = [];
        $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));


        Company::whereNotNull('signme_id')->get()->each(function (Company $company) use (&$companies) {
            $companies[] = $company->signme_id;
        });

        User::whereNotNull('signme_id')->get()->each(function (User $user) use (&$users) {
            $users[] = $user->signme_id;
        });

        foreach ($users as $user) {
            dump('Удаляем юзера, signme_id: ' . $user);
            try {
                $signMe->deleteByRequestId($user);
            } catch (SignMeResponseException | ClientExceptionInterface $e) {
                dump('При удалении' . $user . ' что то пошло не так');
                dump($e->getMessage());
            }
        }

        foreach ($companies as $company) {
            dump('Удаляем компанию, signme_id: ' . $company);
            try {
                $signMe->deleteByRequestId($company);
            } catch (SignMeResponseException | ClientExceptionInterface $e) {
                dump('При удалении' . $company . ' что то пошло не так');
                dump($e->getMessage());
            }
        }
    }
}
