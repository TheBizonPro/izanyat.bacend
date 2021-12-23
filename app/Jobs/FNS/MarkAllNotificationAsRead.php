<?php

namespace App\Jobs\FNS;

use App\Services\Fns\FNSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class MarkAllNotificationAsRead implements ShouldQueue
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
        $answer = $fnsService->readAllNotifications($this->Inn);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        if (Arr::get($answer, 'status') == 'OK') {
            return true;
        }

        throw new \Exception("Ошибка смены статуса оповещения в ПП НПД. API ФНС вернул неопределенный ответ.");

    }
}
