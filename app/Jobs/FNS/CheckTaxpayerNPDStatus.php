<?php

namespace App\Jobs\FNS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Arr;
use App\Models\User;
use App\Services\Fns\FNSService;

class CheckTaxpayerNPDStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $Inn;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($Inn)
    {
        $this->Inn = $Inn;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FNSService $fnsService)
    {
        $Inn = $this->Inn;

        $answer = $fnsService->taxpayerStatusNDP($Inn);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            if (Arr::get($answer, 'Code') == 'TAXPAYER_UNREGISTERED') {
                return false;
            }
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        if (Arr::exists($answer, 'RegistrationTime')) {
            return true;
        }

        throw new \Exception('Ошибка получения статуса налогоплательщика в ПП НПД. API ФНС вернул неопределенный ответ.');
    }
}
