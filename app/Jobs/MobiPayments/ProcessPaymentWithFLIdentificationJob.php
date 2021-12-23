<?php

namespace App\Jobs\MobiPayments;

use App\Models\Company;
use App\Models\Payout;
use App\Models\User;
use App\Services\Adapters\MobiPaymentsServiceAdapter;
use App\Services\LogsService;
use App\Services\MobiService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWithFLIdentificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Payout $payout;

    /**
     * @param Payout $payout
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }


    public function handle(LogsService $logsService)
    {
        $recipient = $this->payout->user;
        $company = $this->payout->project->company;
        $companySigner = $company->signerUser;

        try {
            $client = new MobiService($company);
            $initPersonRegistrationResponse = $client->initPersonRegistration(
                $recipient->lastname,
                $recipient->firstname,
                $recipient->patronymic,
                $recipient->passport_series,
                $recipient->passport_number,
                $recipient->phone,
                $recipient->inn
            );

            //TODO обработку ошибки инициации регистрации

            $logsService->userLog('Получили ответ по инициации регистрации ФЛ: ', $companySigner->id, $initPersonRegistrationResponse);
            $logsService->userLog('Привязка пользователя к компании начата', $companySigner->id, $this->payout->toArray());

            UpdateMobiFLRegistrationStatusJob::dispatch($this->payout);
        } catch (\SoapFault | \Exception $e) {
            $logsService->userLog('Ошибка при инициализации идентификации самозанятого', $companySigner->id, [
                'error' => $e->getMessage(),
                'payout' => $this->payout->toArray()
            ]);
        }
    }
}
