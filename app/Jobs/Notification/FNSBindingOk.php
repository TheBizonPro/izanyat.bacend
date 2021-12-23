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

class FNSBindingOk implements ShouldQueue
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
        $notification->subject = 'Привязка прошла успешно!';
        $notification->text = 'Привязка вашего аккаунта к платформе «Я занят» прошла успешно! Теперь вы можете брать задачи в работу на странице «Мои проекты»."';
        $notification->save();

        $logsService->fnsLog('Отправили уведомление о том, привязка успешно выполнена', $this->user->id);
    }
}
