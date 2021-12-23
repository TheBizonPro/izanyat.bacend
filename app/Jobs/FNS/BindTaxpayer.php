<?php

namespace App\Jobs\FNS;

use App\Jobs\Notification\TaxpayerDataUpdatedFromFNS;
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
use App\Models\User;
use Illuminate\Support\Facades\Log;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class BindTaxpayer implements ShouldQueue
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
        $logsService->fnsLog('Начали привязку пользователя к "Мой Налог"', $this->user->id);

        $answer = $fnsService->taxpayerBind($this->user->inn);

        $logsService->fnsLog('Получили ответ от ФНС', $this->user->id, [
            'response' => $answer->body()
        ]);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {

            if (Arr::get($answer, 'Code') == 'TAXPAYER_ALREADY_BOUND') {
                $this->user->taxpayer_registred_as_npd = true;
                $this->user->taxpayer_binded_to_platform = true;
                $this->user->taxpayer_bind_id = null;
                $this->user->save();

                $logsService->fnsLog('Пользователь уже привязан к "Мой Налог"', $this->user->id);

                return 'already_bound';
            }

            $Message = Arr::get($answer, 'Message');
            $Code = Arr::get($answer, 'Code');
            throw new \Exception($Message . ' (код ошибки: ' . $Code . ')');
        }

        if (Arr::exists($answer, 'Id')) {
            $this->user->taxpayer_registred_as_npd = null;
            $this->user->taxpayer_binded_to_platform = false;
            $this->user->taxpayer_bind_id = $answer['Id'];
            $this->user->save();

            $logsService->fnsLog('Привязка к "Мой Налог" начата, ожидаем подтверждения пользователя', $this->user->id);

            return 'bind_requested';
        }

        throw new \Exception('Ошибка привязки пользователя к партнеру в ПП НПД. API ФНС вернул неопределенный ответ.');
    }
}
