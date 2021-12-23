<?php

namespace App\Jobs\FNS;

use App\Exceptions\NpdIncomeFiscalSoapException;
use App\Services\Fns\FNSService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payout;

/**
 * Перезапускает задачу фискализации.
 * Повторяется если фискализация выдала SoapFault исключение
 *
 * Class CheckIncomeFiscal
 * @package App\Jobs\FNS
 */
class CheckIncomeFiscal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payout;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(FNSService $fnsService)
    {
        $IncomeFiscal= new IncomeFiscalization($this->payout);
        try {
            $IncomeFiscal->handle($fnsService);
        } catch (\SoapFault $e) {
            CheckIncomeFiscal::dispatch($this->payout)->delay(now()->addMinutes(1));

//            $this->payout->saveError('Ошибка фискализации дохода: ' . $e->getMessage());
            throw new NpdIncomeFiscalSoapException($e->getMessage());
        }

        $this->payout->setStatusComplete();
    }
}
