<?php

namespace App\Jobs\Payout;

use App\Jobs\FNS\FiscalIncomeJob;
use App\Jobs\FNS\OfflineFiscal;
use App\Jobs\MobiPayments\ProcessPaymentWithFLIdentificationJob;
use App\Jobs\ProcessMoneyTransferJob;
use App\Models\MobiUserBindings;
use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Payout;
use App\Jobs\FNS\CheckTaxpayerNPDStatus;
use App\Jobs\FNS\GetTaxpayerYearIncome;
use App\Jobs\FNS\IncomeFiscalization;

use App\Jobs\Documents\CreateAct;
use App\Jobs\Documents\CreateReceipt;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class PayoutProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Payout $payout;

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
     * @return bool
     * @throws Exception
     */
    public function handle(LogsService $logsService)
    {
        //TODO сделать под моби
//        $binding = MobiUserBindings::whereUserId($this->payout->user_id)->first() ?? false;
//
//        if (! $binding || ! $binding->is_identified) {
//            ProcessPaymentWithFLIdentificationJob::dispatch($this->payout);
//            $logsService->userLog('Привязка пользователя к компании не найдена, начинаем идентификацию', $this->payout->project->company->signer_user_id, $this->payout->toArray());
//
//            return true;
//        }
//
//
//        ProcessMoneyTransferJob::dispatch($this->payout);
//        $logsService->userLog('Привязка пользователя к компании найдена, начинаем выплату', $this->payout->project->company->signer_user_id, $this->payout->toArray());
//        return true;
    }
}
