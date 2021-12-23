<?php

namespace App\Jobs;

use App\Jobs\FNS\FiscalIncomeJob;
use App\Jobs\MobiPayments\UpdateCompanyBalanceJob;
use App\Models\Payout;
use App\Services\LogsService;
use App\Services\MobiService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateMoneyTransferStatusJob implements ShouldQueue
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

        $client = new MobiService($company);
        $payoutStatusResponse = $client->getPaymentStatus($this->payout->id);

        $logsService->userLog('Платеж в системе "Моби Деньги" обновлен', $companySigner->id, $payoutStatusResponse);


        if (!isset($payoutStatusResponse['PaymentState']) || !isset($payoutStatusResponse['Result'])) {
            //TODO обработку хренового ответа
        }

        // если всё еще в обработке
        if ($payoutStatusResponse['PaymentState'] === 0) {
            self::dispatch($this->payout)->delay(1);
            return;
        }

        // если фейл
        if ($payoutStatusResponse['Result'] === 1) {
            // Log::channel('debug')->debug('фейл трансфера денежек');

            TelegramAdminBotClient::sendAdminNotification($payoutStatusResponse['PaymentStateText'] ?? 'Неизвестная ошибка от МОБИ Деньги');
            $this->payout->update([
                'status' => 'error',
                'description' => $payoutStatusResponse['PaymentStateText'] ?? 'Неизвестная ошибка от МОБИ Деньги'
            ]);
            return;
        }

        // TODO пройтись по коду и убрать магические числа
        if ($payoutStatusResponse['PaymentState'] === 200) {
            $logsService->userLog('Платеж в системе "Моби Деньги" отклонен', $companySigner->id, $payoutStatusResponse);

            SendNotificationJob::dispatch(
                $this->payout->user,
                'Невозможно провести оплату',
                "Заполните платежные данные в личном кабинете для проведения оплаты"
            );
            $logsService->userLog('Отправка уведомления самозанятому о том, что платеж в системе "Моби Деньги" отклонен', $this->payout->user->id, $payoutStatusResponse);

            $this->payout->update([
                'status' => 'error',
                'description' => $payoutStatusResponse['PaymentStateText']
            ]);
            return;
        }

        // если успех
        $this->payout->update([
            'status' => 'complete',
            'description' => 'Платёж выполнен'
        ]);

        $this->payout->task->update([
            'status' => 'paid'
        ]);

        $logsService->userLog('Платеж в системе "Моби Деньги" успешно завершен, платеж и задача обновлены', $companySigner->id);

        UpdateCompanyBalanceJob::dispatch($this->payout->project->company);

        // когда все успешно - запускаем фискализацию
        FiscalIncomeJob::dispatch($this->payout);

        $project =  $this->payout->project;
        $task = $this->payout->task;

        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatch(
                $user,
                'Деньги за задачу отправлены',
                "Задача <a href='/project/{$task->project->id}/tasks/{$task->id}'>{$task->name}</a> успешно оплачена"
            );
        });

        SendNotificationJob::dispatch(
            $this->payout->project->company->signerUser,
            'Деньги за задачу отправлены',
            "Задача <a href='/project/{$this->payout->task->project->id}/tasks/{$this->payout->task->id}'>{$this->payout->task->name}</a> успешно оплачена"
        );

        $logsService->userLog('Отправлено уведомление о том, что платеж в систему "Моби Деньги" успешно завершен', $companySigner->id);

        SendNotificationJob::dispatch(
            $this->payout->user,
            "Оплата задачи #{$this->payout->task->id} {$this->payout->task->name}",
            "Заказчик <a href='/company/" . $this->payout->task->company()->id . "'>{$this->payout->task->company()->name}</a> оплатил задачу <a href='/contractor/tasks/my/{$this->payout->task->id}'>#{$this->payout->task->id} {$this->payout->task->name}</a>  на сумму {$this->payout->sum} рублей.  <a href='/contractor/payouts?payout_id={$this->payout->id}'>Посмотреть подробности платежа</a>"
        );

        $logsService->userLog('Отправлено уведомление о том, что платеж в систему "Моби Деньги" успешно завершен', $this->payout->user_id);

        // Mail::to([
        //     'sindoringg@gmail.com',
        //     // 'pj1v1b4010343@yandex.ru',
        //     $this->payout->user->email
        // ])->send(new MobiPaymentSuccessMail($this->payout));
    }
}
