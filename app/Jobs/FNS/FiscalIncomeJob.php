<?php

namespace App\Jobs\FNS;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use App\Jobs\Documents\CreateReceipt;
use App\Models\Notification;
use App\Models\Payout;
use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class FiscalIncomeJob implements ShouldQueue
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
        $logsService->userLog('Начата фискализация дохода', $this->payout->user_id, $this->payout->toArray());

        try {
            IncomeFiscalization::dispatchSync($this->payout);
        } catch (\SoapFault $e) {
            OfflineFiscal::dispatchSync($this->payout);

            return true;
        } catch (\Exception $e) {
            $logsService->userLog('Ошибка фискализации дохода', $this->payout->user_id, [
                'error' => $e->getMessage()
            ]);

            $this->payout->status = 'error';

            if (mb_strpos($e->getMessage(), 'TAXPAYER_UNREGISTERED') !== false) {
                $this->payout->description = 'Ошибка фискализации дохода: пользователь, которому вы хотите оплатить выполнение задачи не является плательщиком налога на профессиональный доход (НПД) и не может получать доход в рамках платформы «ЯЗанят». Пользователю необходимо встать на учет в качестве НПД и повторить привязку. Пользователь будет уведомлен о порядке действий, необходимых для получения дохода.';

                $notification = new Notification;
                $notification->user_id = $this->payout->user_id;
                $notification->is_readed = false;
                $notification->from = 'Платформа';
                $notification->subject = 'Ошибка фискализации дохода';
                $notification->text = 'При попытке оплатить выполнение задачи произошла ошибка фискализации дохода в ПП НПД. Причина: пользователь (вы) не является плательщиком налога на проффесиональный доход (НПД). Для получения дохода необходимо встать на учет в качестве НПД (подробнее в <a href="/knowledge-base">Базе знаний</a>), затем в системе «Я Занят» необходимо заново выполнить привязку на странице <a href="/contractor/npd-attach">«Привязка к Мой Налог»</a>';
                $notification->plain_text = 'При попытке оплатить выполнение задачи произошла ошибка фискализации дохода в ПП НПД. Причина: пользователь (вы) не является плательщиком налога на проффесиональный доход (НПД). Для получения дохода необходимо встать на учет в качестве НПД (подробнее в Базе знаний), затем в системе «Я Занят» необходимо заново выполнить привязку на странице «Привязка к Мой Налог»';
                $notification->action = [
                    'type' => NotificationTypes::ENTITY,
                    'entity' => NotificationEntities::PAYMENT,
                    'entity_id' => $this->payout->id
                ];
                $notification->save();

            } else if (mb_strpos($e->getMessage(), 'TAXPAYER_UNBOUND') !== false) {

                $this->payout->description = 'Ошибка фискализации дохода: пользователь, которому вы хотите оплатить выполнение задачи не привязан к платформе-партнеру «ЯЗанят» в рамках системы «Мой Налог» (ПП НПД). Пользователю необходимо выполнить привязку к партнеру. Пользователь будет уведомлен о порядке действий, необходимых для получения дохода.';

                $notification = new Notification;
                $notification->user_id = $this->payout->user_id;
                $notification->is_readed = false;
                $notification->from = 'Платформа';
                $notification->subject = 'Ошибка фискализации дохода';
                $notification->text = 'При попытке оплатить выполнение задачи произошла ошибка фискализации дохода в ПП НПД. Причина: пользователь (вы) не привязаны к партнеру «Я Занят» в рамках системы «Мой Налог» (ПП НПД). Вам необходимо заново выполнить привязку на странице <a href="/contractor/npd-attach">«Привязка к Мой Налог»</a>';
                $notification->plain_text = 'При попытке оплатить выполнение задачи произошла ошибка фискализации дохода в ПП НПД. Причина: пользователь (вы) не привязаны к партнеру «Я Занят» в рамках системы «Мой Налог» (ПП НПД). Вам необходимо заново выполнить привязку на странице «Привязка к Мой Налог»';
                $notification->action = [
                    'type' => NotificationTypes::ENTITY,
                    'entity' => NotificationEntities::PAYMENT,
                    'entity_id' => $this->payout->id
                ];
                $notification->save();

            } else {
                $this->payout->description = 'Ошибка фискализации дохода';
            }

            $this->payout->save();
            return false;
        }
        $this->payout->save();

        CreateReceipt::dispatch($this->payout);
    }
}
