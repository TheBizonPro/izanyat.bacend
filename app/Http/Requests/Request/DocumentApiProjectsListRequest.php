<?php

namespace App\Http\Requests\Request;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class DocumentApiProjectsListRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'inn_organization' => 'required',
        ];
    }
}
