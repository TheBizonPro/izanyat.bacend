<?php

namespace App\Http\Requests;

class CreateRoleRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'name' =>  'required'
        ];
    }
}
