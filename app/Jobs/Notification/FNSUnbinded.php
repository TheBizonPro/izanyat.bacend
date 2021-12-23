<?php

namespace App\Jobs\Notification;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Notification;

class FNSUnbinded implements ShouldQueue
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
    public function handle()
    {
        $notification = new Notification;
        $notification->user_id = $this->user->id;
        $notification->is_readed = false;
        $notification->from = 'Платформа';
        $notification->subject = 'Отвязка ПП НПД';
        $notification->text = 'Ваш аккаунт самозанятого был отвязан от платформы «Я Занят» в рамках системы ФНС (Мой Налог). Для продолжения работы в системе “Я занят” необходимо заново выполнить привязку на странице <a href="/contractor/npd-attach">«Привязка к Мой Налог»</a>';
        $notification->plain_text = 'Ваш аккаунт самозанятого был отвязан от платформы «Я Занят» в рамках системы ФНС (Мой Налог). Для продолжения работы в системе “Я занят” необходимо заново выполнить привязку на странице «Привязка к Мой Налог»';
        $notification->action = [
            'type' => NotificationTypes::FOLLOW,
            'entity' => NotificationEntities::LINK,
            'entity_data' => url('/contractor/npd-attach')
        ];
        $notification->save();
    }
}
