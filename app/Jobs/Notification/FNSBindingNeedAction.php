<?php

namespace App\Jobs\Notification;

use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Notification;

class FNSBindingNeedAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogsService $logsService)
    {
        $notification = new Notification;
        $notification->user_id = $this->user->id;
        $notification->is_readed = false;
        $notification->from = 'Платформа';
        $notification->subject = 'Привязка в приложении «Мой Налог»';
        $notification->text = 'Здравствуйте, ' . $this->user->firstname . '!<br>Для связки вашего аккаунта самозанятого с платформой  «Я Занят» необходимо открыть мобильное приложение «Мой Налог» и предоставить доступ сервису «Я занят». Настройки → Партнёры → «Разрешить» действия партнёру «Я занят»<br><br>После получения дохода чеки в ФНС будут сформированы автоматически. Вам не потребуется заходить в приложение «Мой налог» для регистрации полученного дохода. Все чеки вы всегда сможете посмотреть в разделе «Мои платежи»<br><br>Скачать приложения:<br>Для Android - в <a href="https://play.google.com/store/apps/details?id=com.gnivts.selfemployed&hl=ru&gl=US">Google Play</a><br>Для iOS - в <a href="https://apps.apple.com/ru/app/%D0%BC%D0%BE%D0%B9-%D0%BD%D0%B0%D0%BB%D0%BE%D0%B3/id1437518854?l=en">AppStore</a>';
        $notification->save();

        $logsService->fnsLog('Отправили уведомление о том, что необходимо подтвердить привязку к "ЯЗанят" в "Мой Налог"', $this->user->id);
    }
}
