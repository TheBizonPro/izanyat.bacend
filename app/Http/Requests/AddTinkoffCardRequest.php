<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTinkoffCardRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'description' => 'nullable'
        ];
    }
}
