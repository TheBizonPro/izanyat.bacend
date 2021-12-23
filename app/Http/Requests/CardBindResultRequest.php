<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardBindResultRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'CustomerKey' => 'required',
            'RequestKey' => 'required',
            'Success' => 'required',
            'Status' => 'required',
            'PaymentId' => 'required',
            'ErrorCode' => 'required',
            'Pan' => 'required',
            'ExpDate' => 'required',
            'NotificationType' => 'required|nullable',
            'RebillId' => 'required|nullable',
            'Token' => 'required|nullable',
        ];
    }
}
