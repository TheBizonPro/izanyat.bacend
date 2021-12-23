<?php

namespace App\Http\Requests;

class StoreCompanyBankAccountRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'mobi_partner_id',
            'mobi_secret_pass'
        ];
    }
}
