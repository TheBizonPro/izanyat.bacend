<?php

namespace App\Jobs\MobiPayments;

use App\Models\Company;
use App\Services\Adapters\MobiPaymentsServiceAdapter;
use App\Services\LogsService;
use App\Services\MobiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateCompanyBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Company $company;

    /**
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }


    public function handle(LogsService $logsService)
    {
        //        $apiClient = MobiPaymentsServiceAdapter::getForCompany($this->company);
        $apiClient = new MobiService($this->company);
        $balanceResponse = $apiClient->getBalance();

        $logsService->userLog('Обновлен баланс компании', $this->company->signer_user_id, $balanceResponse);


        $this->company->update([
            'balance' => $balanceResponse['Balance']
        ]);
    }
}
