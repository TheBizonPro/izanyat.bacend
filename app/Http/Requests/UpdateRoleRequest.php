<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'name' => 'sometimes'
        ];
    }
}
