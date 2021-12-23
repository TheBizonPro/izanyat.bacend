<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\PlatformInfo;
use App\Services\Fns\FNSService;
use Exception;
use VoltSoft\FnsSmz\FnsSmzApi;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzOfflineService;
use Illuminate\Console\Command;
use VoltSoft\FnsSmz\IncomeData;

/**
 * Команда для тестирования данных оффлайн режима
 *
 * Class TestOfflineModeData
 * @package App\Console\Commands
 */
class TestOfflineModeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-offline-mode-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование входных/выходных данных для Оффлайн мода ФНС';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(FNSService $fnsService)
    {
        $payout = Payout::create([
            'project_id'      => 1,
            'task_id'         => 1,
            'user_id'         => 59,
            'job_category_id' => 5,
            'sum'             => 6666,
            'status'          => 'process'
        ]);

        $payoutCreatedAt = $payout->created_at;
        $npdOfflineKey = $payout->user->npdOfflineKey;

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

//        $masterToken = env('FNS_MASTER_TOKEN');
//        $userToken = "1";
//        $ktirUrl = config('npd.ktir_url');
//        $FnsSmzClient = new FnsSmzClient($masterToken, $userToken, $ktirUrl);
//        $FnsSmzClient->setStoringTempToken(true);
//        $FnsSmzClient->setCacheDir(storage_path('app/cache'));
//        $FnsSmzClient->authIfNecessary(config('npd.auth_url'));
//        $FnsSmzClient->setDebug(true);
//        $FnsSmzClient->setLogFile('app/public/lastfiscal.log');
//        $FnsSmzApi = new FnsSmzApi($FnsSmzClient);

        $services = [
            [
                'Amount' => $payout->sum,
                'Name' => $payout->jobCategory->name,
                'Quantity' => 1
            ]
        ];

        $response = $fnsService->incomeFiscalization(
            $incomeData->inn,
            $fiscalSignature->receiptId,
            $payoutCreatedAt->toAtomString(),
            $payoutCreatedAt->toAtomString(),
            $incomeData->customerInn,
            $payout->project->company->name,
            null,
            $services,
            $incomeData->totalAmount,
            $fiscalSignature->incomeHashCode,
            $payout->getReceiptUrl(true),
        );

        $npdOfflineKey->delete();

        dd($response);
    }
}
