<?php

namespace App\Jobs\MobiPayments;

use App\Jobs\ProcessMoneyTransferJob;
use App\Models\Company;
use App\Models\MobiUserBindings;
use App\Models\Payout;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Services\Adapters\MobiPaymentsServiceAdapter;
use App\Services\LogsService;
use App\Services\MobiPaymentsService;
use App\Services\MobiService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UpdateMobiFLRegistrationStatusJob implements ShouldQueue
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
        $user = $this->payout->user;
        $company = $this->payout->project->company;
        $companySigner = $company->signerUser;

        $logsService->userLog('Обновляем статус регистрации самозанятого', $companySigner->id);

        $company = $this->payout->project->company;
        $client = new MobiService($company);
//        $client = MobiPaymentsServiceAdapter::getForCompany($this->payout->project->company);
        try {
            $resp = $client->getPersonRegistrationStatus(
                $user->lastname,
                $user->firstname,
                $user->patronymic,
                $user->passport_series,
                $user->passport_number,
            );

            $logsService->userLog('Получили ответ по обновлению статусу регистрации самозанятого', $companySigner->id, $resp);


        } catch (\SoapFault | \Exception $e) {
            $logsService->userLog('Получили ответ по обновлению статусу регистрации самозанятого', $companySigner->id, [
                'error' => $e->getMessage()
            ]);

            //TODO обработку ошибки запроса
            return;
        }

        // валидация ответа
        if (! isset($resp['RegistrationState']) || ! isset($resp['Result'])){
            //TODO в ответе шляпа, залогировать и зафейлить
            $logsService->userLog('Ответ от Моби Деньги по обновлению статусу регистрации самозанятого не соответствует стандарту', $companySigner->id, $resp);
        }

        if ($resp['Result'] === 1) {
            //земля пухом, чел не прошел идентификацию в моби деньгах
            //TODO обработку фейла идентификации

            $logsService->userLog('Самозанятый не прошел идентификацию', $companySigner->id, $resp);
        }

        if ($resp['RegistrationState'] === 100 && $resp['Result'] === 0) {
            Log::channel('debug')->debug('Регистрация прошла успешно');
            $logsService->userLog('Идентификация самозанятого в "Моби Деньги" прошла успешно', $companySigner->id, $resp);

            MobiUserBindings::create([
                'user_id' => $user->id,
                'company_id' => $this->payout->project->company_id,
                'mobi_confirm_id' => $resp['ClientConfirmID'] ?? NULL,
                'is_identified' => 1,
            ]);

            $logsService->userLog('Привязка самозанятого к компании в рамках системы платежей "Моби Деньги" создана', $companySigner->id, $resp);

            ProcessMoneyTransferJob::dispatch($this->payout);

            return;
        }

        if ($resp['RegistrationState'] === 0) {
            self::dispatch($this->payout)->delay(1);
        }
    }
}
