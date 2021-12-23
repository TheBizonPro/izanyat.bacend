<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBankAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobiService
{
    private string $host;
    private string $mobiPartnerID;
    private string $mobiSecretPassword;

    public function __construct($company, string $partnerID = null, string $partnerSecret = null)
    {
        if ($partnerID !== null && $partnerSecret !== null && $company === null) {
            $this->mobiPartnerID = $partnerID;
            $this->mobiSecretPassword = $partnerSecret;
            $this->host = "https://mobi.izanyat.ru/api";
        } else {
            $companyBankAccount = CompanyBankAccount::whereCompanyId($company->id)->firstOrFail();
            $this->mobiPartnerID = $companyBankAccount->mobi_partner_id;
            $this->mobiSecretPassword = $companyBankAccount->mobi_secret_password;
            $this->host = "https://mobi.izanyat.ru/api"; //config('microservice.mobi_microservice');
        }
    }

    public function initPersonRegistration(
        string $lastname,
        string $firstname,
        string $patronymic,
        string $passportSeries,
        string $passportNumber,
        string $phone,
        string $inn
    ) {
        $address = "{$this->host}/person/registration";
        Log::channel('debug')->debug('TEST Registration Person');
        return Http::asForm()->post($address, [
            'partner_id' => $this->mobiPartnerID,
            'secret_password' => $this->mobiSecretPassword,
            'lastname' => $lastname,
            'firstname' => $firstname,
            'patronymic' => $patronymic,
            'passport_series' => $passportSeries,
            'passport_number' => $passportNumber,
            'phone' => $phone,
            'inn' => $inn
        ])->json();
    }

    public function getPersonRegistrationStatus(
        string $lastname,
        string $firstname,
        string $patronymic,
        string $passportSeries,
        string $passportNumber
    ) {
        $address = "{$this->host}/person/status";
        return Http::asForm()->post($address, [
            'partner_id' => $this->mobiPartnerID,
            'secret_password' => $this->mobiSecretPassword,
            'lastname' => $lastname,
            'firstname' => $firstname,
            'patronymic' => $patronymic,
            'passport_series' => $passportSeries,
            'passport_number' => $passportNumber,
        ])->json();
    }

    public function getBalance()
    {
        $address = "{$this->host}/person/balance";
        return Http::asForm()->post($address, [
            'partner_id' => $this->mobiPartnerID,
            'secret_password' => $this->mobiSecretPassword,
        ])->json();
    }

    public function initPaymentEasy(
        $orderID,
        $paymentType,
        $amount,
        $reason,
        $offerAccepted,
        $clientConfirmProvided,
        $cardNumber,
        $lastName,
        $firstName,
        $middleName,
        $docSer,
        $docNumber
    ) {
        $address = "{$this->host}/payment/init";
        return Http::asForm()->post($address, [
            'partner_id' => $this->mobiPartnerID,
            'secret_password' => $this->mobiSecretPassword,
            'order_id' => $orderID,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'reason' => $reason,
            'offer_accepted' => $offerAccepted,
            'client_confirm_provided' => $clientConfirmProvided,
            'card_number' => $cardNumber,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'doc_ser' => $docSer,
            'doc_number' => $docNumber,
        ])->json();
    }

    public function getPaymentStatus($orderID)
    {
        $address = "{$this->host}/payment/status";

        return Http::asForm()->post($address, [
            'partner_id' => $this->mobiPartnerID,
            'secret_password' => $this->mobiSecretPassword,
            'order_id' => $orderID,
        ])->json();
    }
}
