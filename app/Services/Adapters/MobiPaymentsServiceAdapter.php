<?php

namespace App\Services\Adapters;

use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Services\MobiPaymentsService;
use function GuzzleHttp\Psr7\str;

class MobiPaymentsServiceAdapter
{
    public static function getForCompany(Company $company): MobiPaymentsService
    {
        $companyBankAccount = CompanyBankAccount::whereCompanyId($company->id)->firstOrFail();

        return new MobiPaymentsService($companyBankAccount->mobi_partner_id, $companyBankAccount->mobi_secret_password);
    }

    public static function makeValidPhoneNumber(int $phoneNumber): string
    {
        $validPhone = (string) $phoneNumber;

        if (str_starts_with($validPhone, '7')) {
            $validPhone = substr($validPhone, 1);
        }

        return $validPhone;
    }
}
