<?php

namespace App\Jobs\FNS;

use App\Jobs\Documents\CreateReceipt;
use App\Models\NpdOfflineKeys;
use App\Models\PlatformInfo;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payout;
use VoltSoft\FnsSmz\FnsSmzOfflineService;
use VoltSoft\FnsSmz\IncomeData;

/**
 * Формирует оффлайн чек,
 * Запускает проверку фискализации
 *
 * Class OfflineFiscal
 * @package App\Jobs\FNS
 */
class OfflineFiscal implements ShouldQueue
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
    public function handle()
    {
        $payout = $this->payout;

        $payoutCreatedAt = $payout->created_at;
        $npdOfflineKey = NpdOfflineKeys::whereUserId($payout->user_id)->firstOrFail();

        $incomeData = new IncomeData(
            $payout->user->inn,
            $payoutCreatedAt,
            $payoutCreatedAt,
            $payout->sum,
            $npdOfflineKey->hash_key,
            $npdOfflineKey->sequence_number,
            $payout->project->company->inn,
            PlatformInfo::getPartnerId(),
            config('npd.source_device_id')
        );

        $offlineService = new FnsSmzOfflineService();

        $fiscalSignature = $offlineService->generateFiscalSignature($incomeData);
        $payout->setOfflineReceiptData($fiscalSignature);

        CreateReceipt::dispatch($payout);

        $npdOfflineKey->delete();

        $payout->calculateBalance();
        $payout->setStatusComplete();
        $payout->save();

        CheckIncomeFiscal::dispatch($payout);
    }
}
