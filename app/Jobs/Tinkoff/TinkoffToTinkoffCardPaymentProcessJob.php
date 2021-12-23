<?php

namespace App\Jobs\Tinkoff;

use App\Jobs\SendNotificationJob;
use App\Models\Payout;
use App\Models\UserPaymentMethod;
use App\Services\LogsService;
use App\Services\TinkoffPaymentsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TinkoffToTinkoffCardPaymentProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Payout $payout;

    public function handle(TinkoffPaymentsApiService $paymentsApiService, LogsService $logsService)
    {
        $companySigner = $this->payout->project->company->signerUser;
        $contractorCard = UserPaymentMethod::findOrFail($this->payout->task->contractor_payment_method_id)
            ->toTinkoffCard();

        try {
            $initResponse = $paymentsApiService->initPayment([
                'order_id' => $this->payout->id,
                'card_id' => $contractorCard->card_id,
                'amount' => $this->payout->sum * 100, // умножаем на 100 т.к сумма в копейках
                'customer_key' => $contractorCard->tinkoffBankAccount->tinkoff_customer_key
            ]);

            if (! $initResponse['Success']) {
                $logsService->tinkoffLog("Ошибка при попытке отправить деньги по платежу {$this->payout->id}.", $companySigner->id, [
                    'message' => $initResponse['Message'] ?? '-',
                    'details' => $initResponse['Details'] ?? '-',
                ]);

                SendNotificationJob::dispatch(
                    $companySigner,
                    'Ошибка оплаты',
                    "При выплате самозанятому по платежу №{$this->payout->id} произошла ошибка",
                    "При выплате самозанятому по платежу №{$this->payout->id} произошла ошибка"
                );

                return;
            }

            $paymentsApiService->payment($initResponse['PaymentId']);




        } catch (\Exception $e) {
            //todo
        }

        //TODO payment state checking
        //TODO payment state result handling
    }
}
