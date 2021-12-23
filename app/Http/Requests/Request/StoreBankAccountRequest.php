<?php

namespace App\Http\Requests\Request;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'card_number' => 'required',
        ];
    }
}
