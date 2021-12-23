<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserToProjectRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'projects' => 'array|nullable'
        ];
    }
}
