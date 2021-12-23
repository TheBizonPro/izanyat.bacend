<?php

namespace App\Http\Requests\Request;

use App\Http\Requests\ApiRequest;

class DocumentApiUploadRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'inn' => 'required',
            'document' => 'required',
            'date' => 'required',
            'number' => 'required',
            'project_id' => 'required'
        ];
    }
}
