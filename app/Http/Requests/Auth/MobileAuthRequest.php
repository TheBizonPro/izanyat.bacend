<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class MobileAuthRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone_number' => 'required',
            'password' => 'required',
            'device_name' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'phone_number.required' => 'Введите номер телефона',
            'password.required' => 'Введите пароль',
            'device_name.required' => 'Введите название устройства',
        ];
    }
}
