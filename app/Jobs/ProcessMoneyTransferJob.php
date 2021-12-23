<?php

namespace App\Jobs;

use App\Models\Payout;
use App\Services\LogsService;
use App\Services\MobiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMoneyTransferJob implements ShouldQueue
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
        $company = $this->payout->project->company;
        $companySigner = $company->signerUser;

        $logsService->userLog('Начат трансфер денег самозанятому', $companySigner->id, $this->payout->toArray());

        $recipient = $this->payout->user;

        $payoutStatus = 'process';
        $payoutDescription = 'Платёж находится в обработке';

        $apiClient = new MobiService($company);
        try {
            $resp = $apiClient->initPaymentEasy(
                $this->payout->id,
                'easycardposxml',
                $this->payout->sum,
                $this->payout->task->name,
                true,
                true,
                $recipient->bankAccount->card_number,
                $recipient->lastname,
                $recipient->firstname,
                $recipient->patronymic,
                $recipient->passport_series,
                $recipient->passport_number
            );

            $logsService->userLog('Выполнена инициализация платежа самозанятому', $companySigner->id, [
                'payout' => $this->payout->toArray(),
                'data_send_to_mobi' => [
                    'OrderID' => $this->payout->id,
                    'PaymentType' => 'easycardposxml',
                    'Amount' => $this->payout->sum,
                    'Reason' => $this->payout->task->name,
                    'OfferAccepted' => true,
                    'ClientConfirmProvided' => true,
                    'Recipient' => [
                        'CardNumber' => $recipient->bankAccount->card_number,
                        'LastName' => $recipient->lastname,
                        'FirstName' => $recipient->firstname,
                        'MiddleName' => $recipient->patronymic,
                        'DocSer' => $recipient->passport_series,
                        'DocNumber' => $recipient->passport_number,
                    ]
                ],
                'mobi_resp' => $resp
            ]);
        } catch (\Exception $e) {
            $logsService->userLog('Ошибка трансфера денег самозанятому', $companySigner->id, [
                'payout' => $this->payout->toArray(),
                'error' => $e->getMessage()
            ]);

            $payoutStatus = 'error';
            $payoutDescription = 'Ошибка при отправке платежа в МОБИ Деньги';
        }

        if ($resp['Result'] === 2) {
            $payoutStatus = 'error';
            $payoutDescription = $resp['ResultText'] ?? $payoutDescription;
        }

        //TODO чек ошибки отправки платежа И ЛОГ

        $this->payout->update([
            'status' => $payoutStatus,
            'description' => $payoutDescription,
        ]);

        $logsService->userLog('Платеж обновлен', $companySigner->id, $this->payout->toArray());

        if ($resp['Result'] === 0)
            UpdateMoneyTransferStatusJob::dispatch($this->payout);
    }
}
