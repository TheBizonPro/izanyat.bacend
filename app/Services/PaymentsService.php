<?php

namespace App\Services;

use App\Jobs\MobiPayments\UpdateCompanyBalanceJob;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Services\Telegram\TelegramAdminBotClient;

class PaymentsService
{
    /**
     * @throws \Exception
     */
    public function getMobiCompanyBalance(string $companyId, string $companySecretPassword): array
    {
//        $company = Company::findOrFail($companyId);

        $apiClient = new MobiService(null, $companyId, $companySecretPassword);

        return $apiClient->getBalance();
    }


    /**
     * @throws \Exception
     */
    public function setCompanyCredentials(Company $company, string $companyMobiId, string $companySecretPassword): CompanyBankAccount
    {
        $companyBalanceResponse = $this->getMobiCompanyBalance($companyMobiId, $companySecretPassword);

        if (! isset($companyBalanceResponse['Balance'])) {
            throw new \Exception('Получен неопределенный ответ от МОБИ Деньги');
        }

        $companyBankAccountData = [
            'mobi_partner_id' => $companyMobiId,
            'mobi_secret_password' => $companySecretPassword,
            'mobi_connected' => 1,
            'company_id' => $company->id
        ];

        $companyBankAccount = CompanyBankAccount::updateOrCreate([
            'company_id' => $company->id
        ], $companyBankAccountData);

        $company->refresh();

        UpdateCompanyBalanceJob::dispatch($company);

        return $companyBankAccount;
    }
}
