<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorAssignTaskPaymentMethodRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|numeric'
        ];
    }
}
