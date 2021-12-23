<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\LogsService;
use App\Services\TinkoffPaymentsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TinkoffContractorRegistrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function handle(TinkoffPaymentsApiService $tinkoffPayments, LogsService $logsService)
    {
        $user = $this->user;
        $logsService->tinkoffLog('Начинаем привязку пользователя', $user->id);

        $response = $tinkoffPayments->addCustomer([
            'customer_id' => $user->id,
            'email' => $user->email,
            'phone' => '+' . $user->phone,
        ]);

        $logsService->tinkoffLog('Получили ответ по привязке пользователя', $user->id, $response);

    }
}
