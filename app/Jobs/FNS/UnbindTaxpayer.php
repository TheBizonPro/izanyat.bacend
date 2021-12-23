<?php

namespace App\Jobs\FNS;

use App\Services\Fns\FNSService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Arr;
use App\Models\User;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class UnbindTaxpayer implements ShouldQueue
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
    public function handle(FNSService $fnsService)
    {
        $answer = $fnsService->taxpayerUnbind($this->user->inn);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Code = Arr::get($answer, 'Code');
            $Message = Arr::get($answer, 'Message');


            if ($Code == 'TAXPAYER_UNBOUND') {
                $this->user->taxpayer_registred_as_npd = null;
                $this->user->taxpayer_binded_to_platform = false;
                $this->user->taxpayer_income_limit_not_exceeded = null;
                $this->user->save();

                return true;
            }

            throw new \Exception($Message . ' (код ошибки: ' . $Code . ')');
        }

        if (Arr::exists($answer, 'UnregistrationTime')) {
            $this->user->taxpayer_registred_as_npd = null;
            $this->user->taxpayer_binded_to_platform = false;
            $this->user->taxpayer_income_limit_not_exceeded = null;
            $this->user->taxpayer_bind_id = null;
            $this->user->save();

            return true;
        }

        throw new \Exception("Ошибка отвязки пользователя от партнера в ПП НПД. API ФНС вернул неопределенный ответ.");
    }
}
