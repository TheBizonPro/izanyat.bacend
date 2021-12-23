<?php

namespace App\Jobs\FNS;

use App\Models\User;
use App\Services\Fns\FNSService;
use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

use App\Jobs\Notification\TaxpayerDataUpdatedFromFNS as TaxpayerDataUpdatedFromFNSNotification;

class UpdateTaxpayerData implements ShouldQueue
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
    public function handle(FNSService $fnsService, LogsService $logsService)
    {
        $logsService->fnsLog('Начали сверку данных, введенных пользователем с его данными из "Мой Налог"', $this->user->id);
        $answer = $fnsService->taxpayerStatus($this->user->inn);
        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        $userUpdated = false;

        $FirstName = null;
        $SecondName = null;
        $Patronymic = null;

        if (Arr::exists($answer, 'FirstName')) {
            $FirstName = $answer['FirstName'];
            if (mb_strtolower($this->user->firstname) != mb_strtolower($FirstName)) {
                $this->user->firstname = $FirstName;
                $userUpdated = true;

                $logsService->userLog('При привязке к "Мой Налог" не совпали имена, заменяем данные пользователя на полученные из ФНС', $this->user->id, []);
            }
        }


        if (Arr::exists($answer, 'SecondName')) {
            $SecondName = $answer['SecondName'];
            if (mb_strtolower($this->user->lastname) != mb_strtolower($SecondName)) {
                $this->user->lastname = $SecondName;
                $userUpdated = true;
                $logsService->userLog('При привязке к "Мой Налог" не совпали фамилии, заменяем данные пользователя на полученные из ФНС', $this->user->id, []);
            }
        }


        if (Arr::exists($answer, 'Patronymic')) {
            $Patronymic = $answer['Patronymic'];
            if (mb_strtolower($this->user->patronymic) != mb_strtolower($Patronymic)) {
                $this->user->patronymic = $Patronymic;
                $userUpdated = true;
                $logsService->userLog('При привязке к "Мой Налог" не совпали отчества, заменяем данные пользователя на полученные из ФНС', $this->user->id, []);
            }
        }

        if ($userUpdated == true) {
            $this->user->save();

            TaxpayerDataUpdatedFromFNSNotification::dispatch(
                $this->user,
                $FirstName,
                $SecondName,
                $Patronymic
            );
        }


        return true;

        // throw new \Exception("Ошибка отвязки пользователя от партнеру в ПП НПД. API ФНС вернул неопределенный ответ.");
    }
}
