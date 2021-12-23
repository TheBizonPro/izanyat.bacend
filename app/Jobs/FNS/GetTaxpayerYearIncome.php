<?php

namespace App\Jobs\FNS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FNS;
use Illuminate\Support\Arr;
use App\Services\Fns\FNSService;
use App\Models\User;

use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

use Carbon\Carbon;

class GetTaxpayerYearIncome implements ShouldQueue
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

        /**
         * Получим периоды с начала года.
         */
        $date_from = new Carbon(new \DateTime('first day of january this year'));
        $date_till = new Carbon();
        $TaxPeriods = [];
        $dt = clone $date_from;
        while ($dt < $date_till) {
            $TaxPeriods[] = $dt->format('Ym');
            $dt->modify('+1 month');
        }

        /**
         * Получим суммарный доход с начала года.
         */
        $summaryIncome = 0;
        foreach ($TaxPeriods as $TaxPeriodId) {
            $answer = $fnsService->yearIncome($Inn, $TaxPeriodId);

            if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
                $Message = Arr::get($answer, 'Message');
                throw new \Exception($Message);
            }

            if (Arr::exists($answer, 'TotalAmount') == false) {
                throw new \Exception('Ошибка получения суммарного дохода в ПП НПД. API ФНС вернул неопределенный ответ.');
            }

            $summaryIncome+= floatval(Arr::get($answer, 'TotalAmount'));
        }

        return $summaryIncome;

    }
}
