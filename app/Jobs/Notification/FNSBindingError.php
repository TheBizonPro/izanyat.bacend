<?php

namespace App\Jobs\Notification;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Notification;

class FNSBindingError implements ShouldQueue
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
        $notification->subject = 'Ошибка привязки ';
        $notification->text = 'Здравствуйте, ' . $this->user->firstname . '!<br>При попытке связать ваш аккаунт самозанятого с платформой «Я Занят» в системе ФНС произошла ошибка. Чтобы брать заказы в системе “Я занят” вам необходимо выполнить привязку вручную на странице <a href="/contractor/npd-attach">«Привязка к Мой Налог»</a>';
        $notification->plain_text = 'Здравствуйте, ' . $this->user->firstname . '! При попытке связать ваш аккаунт самозанятого с платформой «Я Занят» в системе ФНС произошла ошибка. Чтобы брать заказы в системе “Я занят” вам необходимо выполнить привязку вручную на странице «Привязка к Мой Налог»';
        $notification->action = [
            'type' => NotificationTypes::FOLLOW,
            'entity' => NotificationEntities::LINK,
            'entity_data' => url('/contractor/npd-attach')
        ];
        $notification->save();

        $logsService->fnsLog('Отправили уведомление о том, при привязке к "Мой Налог" произошла ошибка', $this->user->id);
    }
}
