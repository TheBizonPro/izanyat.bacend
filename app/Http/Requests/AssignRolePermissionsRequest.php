<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignRolePermissionsRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'permissions' => 'array|required'
        ];
    }
}
