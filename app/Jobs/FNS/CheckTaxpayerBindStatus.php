<?php

namespace App\Jobs\FNS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use App\Services\Fns\FNSService;
use Illuminate\Support\Facades\Log;


class CheckTaxpayerBindStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $Id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($Id)
    {
        $this->Id = $Id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FNSService $fnsService)
    {
        $answer = $fnsService->taxpayerBindStatus($this->Id);
        Log::channel()->debug("BIND STATUS {$answer->body()}");
        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        if (Arr::exists($answer, 'Result')) {
            $Result = Arr::get($answer, 'Result');
            $statuses = ['COMPLETED', 'FAILED', 'IN_PROGRESS'];
            if (in_array($Result, $statuses)) {
                return $Result;
            }
        }

        throw new \Exception('Ошибка проверки статуса привязки налогоплательщика к партнеру в ПП НПД. API ФНС вернул неопределенный ответ.');
    }
}
