<?php

namespace App\Console\Commands;

use App\Jobs\MobiPayments\UpdateCompanyBalanceJob;
use App\Models\Company;
use Illuminate\Console\Command;

class  UpdateCompanyBalance extends Command
{

    protected $signature = 'mobi:balance';

    protected $description = 'Update company balance';

    public function handle()
    {
        $company = Company::whereHas('bankAccount')->first();

        $result = UpdateCompanyBalanceJob::dispatchSync($company);
    }
}
