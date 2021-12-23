<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignManyRolesToUserRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'roles' => 'array|required'
        ];
    }
}
