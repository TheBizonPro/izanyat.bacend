<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterContractorRequest extends FormRequest
{
    public function rules()
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'patronymic' => 'required',
            'email' => 'required|unique:users,email',
            'inn' => 'required|min:12|max:12|unique:users,inn',
        ];
    }

    public function messages()
    {
        return [
            'firstname.required' => 'Введите имя',
            'lastname.required' => 'Введите фамилию',
            'patronymic.required' => 'Введите отчество',
            'email.required' => 'Введите Email',
            'email.unique' => 'Пользователь с таким Email уже зарегистрирован',
            'inn.required' => 'Введите ИНН',
            'inn.min' => 'ИНН должен состоять из 12 символов',
            'inn.max' => 'ИНН должен состоять из 12 символов',
            'inn.unique' => 'Пользователь с таким ИНН уже зарегистрирован',
        ];
    }
}
