<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePersonalDataRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'email'               => ['email', Rule::unique('users', 'email')->ignore(\Auth::user()->id, 'id')],
            'sex'                 => [Rule::in(['m', 'f'])],
            'birth_place'         => [],
            'birth_date'          => ['date_format:d.m.Y'],
            'passport_series'     => ['digits:4'],
            'passport_number'     => ['digits:6'],
            'passport_code'       => ['fms_code'],
            'passport_issuer'     => [],
            'passport_issue_date' => ['date_format:d.m.Y'],
            'snils'               => ['snils'],
            'inn'                 => ['inn', Rule::unique('users', 'inn')->ignore(\Auth::user()->id, 'id')],
            'address_region'      => [],
            'address_city'        => [],
            'address_street'      => [],
            'address_house'       => [],
            'address_building'    => [],
            'address_flat'        => [],
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Такой email уже используется в системе',
            'snils.snils' => 'СНИСЛ невалидный',
            'inn.unique' => 'Такой ИНН уже зарегистрирован в системе',
            'inn.inn' => 'ИНН невалидный',
        ];
    }
}
