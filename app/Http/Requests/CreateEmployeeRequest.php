<?php

namespace App\Http\Requests;

use App\Models\User;

class CreateEmployeeRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'patronymic' => 'required',
            'phone' => 'required|unique:users,phone',
        ];
    }

    public function messages()
    {
        return [
            'phone.unique' => 'Этот номер телефона уже использутеся в системе.',
            'firstname.required' => 'Имя обязательно для заполнения',
            'lastname.required' => 'Фамилия обязательна для заполнения',
            'patronymic.required' => 'Отчество обязательно для заполнения',
            'phone.required' => 'Номер телефона обязателен для заполнения',
        ];
    }

    public function getValidatorInstance()
    {
        $this->formatPhoneNumber();

        return parent::getValidatorInstance();
    }

    protected function formatPhoneNumber()
    {
        if ($this->request->has('phone')) {
            $this->merge([
                'phone' => User::formatPhone($this->request->get('phone'))
            ]);
        }
    }
}
