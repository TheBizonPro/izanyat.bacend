<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyAssignTaskPaymentMethodRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => 'required'
        ];
    }
}
