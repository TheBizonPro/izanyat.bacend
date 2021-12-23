<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignProjectToUsersRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'users' => 'array|nullable'
        ];
    }
}
