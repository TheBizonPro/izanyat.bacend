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
use Log;

class TaxpayerDataUpdatedFromFNS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private ?string $Firstname;
    private ?string $Lastname;
    private ?string $Patronymic;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, ?string $Firstname = null, ?string $Lastname = null, ?string $Patronymic = null)
    {
        $this->user = $user;
        $this->Firstname = $Firstname;
        $this->Lastname = $Lastname;
        $this->Patronymic = $Patronymic;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogsService $logsService)
    {
        $string = "Ваши персональные данные, указанные в платформе не соответствовали данным из системы «Мой Налог» ФНС. Согласно регламенту, мы автоматически обновили персональные данные";

        if (!is_null($this->Firstname)) {
            $string .= "</br> - <b>Имя</b>";
        }
        if (!is_null($this->Lastname)) {
            $string .= "</br> - <b>Фамилия</b>";
        }
        if (!is_null($this->Patronymic)) {
            $string .= "</br> - <b>Отчество</b>";
        }

        $notification = new Notification;
        $notification->user_id = $this->user->id;
        $notification->is_readed = false;
        $notification->from = 'Платформа';
        $notification->subject = 'Данные обновлены ПП НПД';
        $notification->text = $string;
        $notification->plain_text = 'Ваши персональные данные, указанные в платформе не соответствовали данным из системы «Мой Налог» ФНС. Согласно регламенту, мы автоматически обновили персональные данные в платформе «Я Занят» в соответствии с данными из системы «Мой Налог» ФНС.';
        $notification->save();

        $logsService->userLog('При привязке к "Мой Налог" не совпали ФИО пользователя, шлем ему уведомление о том, что данные были обновлены на актуальные из ФНС', $this->user->id, []);
    }
}
