<?php

namespace App\Services;

use App\Exceptions\UserRegistrationExeption;
use App\Logging\LogsHelper;
use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\SendNotificationJob;

class UsersService
{
    /**
     * @throws UserRegistrationExeption
     */
    public function registerUser(array $registerData, User $me): User
    {
        if (!isset($registerData['registration_type']))
            throw new UserRegistrationExeption('Ошибка валидации данных', 422, ['Не указан тип регистрации']);

        if (isset($registerData['user']['snils'])) {
            $registerData['user']['snils'] = preg_replace("/\D/", "", trim($registerData['user']['snils']));
        }

        $validator = $this->validateUserData($registerData['user'], $registerData['registration_type']);

        if ($validator->fails()) {
            throw new UserRegistrationExeption('Ошибка валидации данных пользователя', 422, $validator->messages()->all());
        }

        if ($registerData['registration_type'] == 'client') {
            $validator = $this->validateCompanyData($registerData['company']);

            if ($validator->fails()) {
                throw new UserRegistrationExeption('Ошибка валидации данных пользователя', 422, $validator->messages()->all());
            }
        }

        $logger = LogsHelper::createFsLogger();

        DB::beginTransaction();

        $logger->userLog('Пользователь начал регистрацию', $me->id, $me->toArray());

        if ($registerData['registration_type'] == 'client') {
            $me->is_client = 1;
            $company = new Company;
            $company->fill($registerData['company']);
            $company->save();

            $logger->userLog('Пользователь является представителем компании, компания создана и сохранена', $me->id, $me->toArray());
        }

        if ($registerData['registration_type'] == 'contractor') {
            $me->is_selfemployed = 1;
            $me->company_id = null;
            $logger->userLog('Пользователь является самозанятым', $me->id, $me->toArray());
        }

        $me->fill($registerData['user']);

        $me->save();
        $logger->userLog('Данные пользователя сохранены', $me->id, $me->toArray());

        if ($registerData['registration_type'] == 'client') {
            UserCompany::insert(['user_id' => $me->id, 'company_id' => $company->id]);
            $me->company_id = $company->id;
            $me->save();

            $company->signer_user_id = $me->id;
            $company->save();
            $company->refresh();

            // ставим глобальный ид тимы для спати
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);

            \App\Helpers\PermissionsHelper::registerCompanyAdmin($company);


            $logger->userLog('Привязали компанию к представителю', $me->id);

            $logger->userLog('Назначили человека, регистрирующего компанию админом', $me->id);
        }

        DB::commit();

        $profileLink = $me->is_selfemployed ? '/my' : '/my-company';

        SendNotificationJob::dispatch(
            $me,
            'Регистрация прошла успешно',
            "Для получения или отправки выплат необходимо перейти в <a href='$profileLink'>профиль</a> и заполнить данные для платежей "
        );

        $logger->userLog('Отправили уведомление о необходимости заполнения платежных данных', $me->id);


        return $me;
    }

    public function createBasicUser(int $phoneNumber)
    {
        $user = new User();
        $user->phone = $phoneNumber;

        $user->save();

        return $user;
    }

    public function generateConfirmationSMSText(string $code): string
    {
        return 'Ваш код для "Я Занят": ' . $code;
    }

    public function validateCompanyData(array $companyData)
    {
        $companyValidateRules = [
            'full_name' => ['required'],
            'name' => ['required'],
            'legal_address' => ['required'],
            'fact_address' => ['required'],
            'inn' => ['required', 'inn', Rule::unique('companies', 'inn')],
            'ogrn' => ['required', 'regex:/^[0-9]{13,15}$/', Rule::unique('companies', 'ogrn')],
            'okpo' => ['required', 'regex:/^[0-9]{8,10}$/'],
            'kpp' => ['required', 'digits:9'],
            'phone' => ['required', Rule::unique('companies', 'phone')],
            'email' => ['required', 'email', Rule::unique('companies', 'email')],
            'address_region' => ['required'],
            'address_city' => ['required'],
        ];

        $companyErrorMessages = [
            'full_name.required'      => 'не указано полное название компании',
            'name.required'           => 'не указано краткое название компании',
            'legal_address.required'  => 'не указан юридический адрес компании',
            'fact_address.required'   => 'не указан фактический адрес компании',
            'inn.required'            => 'не указан ИНН компании',
            'inn.inn'                 => 'неверный формат ИНН компании (10 или 12 цифр)',
            'inn.unique'              => 'этот ИНН компании уже зарегистрирован в системе',
            'ogrn.required'           => 'не указан ОГРН компании',
            'ogrn.regex'              => 'неверный формат ОГРН компании (13 или 15 цифр)',
            'ogrn.unique'             => 'этот ОГРН компании уже зарегистрирован в системе',
            'okpo.required'           => 'не указан ОКПО компании',
            'okpo.regex'              => 'неверный формат ОКПО компании (8 или 10 цифр)',
            'kpp.required'            => 'не указан КПП компании',
            'kpp.digits'              => 'неверный формат КПП компании',
            'phone.required'          => 'не указан телефон копмании',
            'phone.unique'            => 'этот телефон копмании уже используется в системе',
            'email.required'          => 'не указан email копмании',
            'email.email'             => 'неверный формат email копмании',
            'email.unique'            => 'этот email копмании уже используется',
            'address_region.required' => 'не указан регион компании',
            'address_city.required'   => 'не указан город компании',
        ];

        return Validator::make($companyData, $companyValidateRules, $companyErrorMessages);
    }

    public function validateUserData(array $userData, $registrationType)
    {
        $userValidateRules = [
            'email'               => ['required', 'email', Rule::unique('users', 'email')],
            'inn'                 => ['required', 'inn', Rule::unique('users', 'inn')],
            'firstname'           => ['required', 'alpha_dash'],
            'lastname'            => ['required', 'alpha_dash'],
            'patronymic'          => ['required', 'alpha_dash'],
            'sex'                 => ['required', Rule::in(['m', 'f'])],
            'birth_place'         => ['required'],
            'birth_date'          => ['required', 'date_format:d.m.Y'],
            'passport_series'     => ['required', 'digits:4'],
            'passport_number'     => ['required', 'digits:6'],
            'passport_code'       => ['required', 'fms_code'],
            'passport_issuer'     => ['required'],
            'passport_issue_date' => ['required', 'date_format:d.m.Y'],
            'snils'               => ['required', 'snils'],
            'address_region'      => ['required'],
            'address_city'        => ['required'],
            'address_street'      => ['required'],
            'address_house'       => ['required'],
            'address_building'    => [''],
            'address_flat'        => [''],
        ];

        if ($registrationType == 'contractor') {
            $userValidateRules['job_category_id'] = ['required', 'integer'];
        }

        $userErrorMessages = [
            'email.required'                  => 'не указан email пользователя',
            'email.email'                     => 'email пользователя указан с ошибкой',
            'email.unique'                    => 'email пользователя уже используется',
            'inn.required'                    => 'не указан ИНН пользователя',
            'inn.inn'                         => 'ИНН пользователя указан не верно',
            'inn.unique'                      => 'Пользователь с таким ИНН уже зарегистрирован в системе',
            'firstname.required'              => 'не указано имя пользователя',
            'firstname.alpha_dash'            => 'имя содержит недопустимые символы',
            'lastname.required'               => 'не указана фамилия пользователя',
            'lastname.alpha_dash'             => 'фамилия содержит недопустимые символы',
            'patronymic.required'             => 'не указано отчество пользователя',
            'patronymic.alpha_dash'           => 'отчество содержит недопустимые символы',
            'sex.required'                    => 'не указан пол пользователя',
            'sex.in'                          => 'пол пользователя передан с ошибкой',
            'birth_place.required'            => 'не указано место рождения пользователя',
            'birth_date.required'             => 'не указана дата рождения пользователя',
            'birth_date.date_format'          => 'неверный формат даты рождения пользователя',
            'passport_series.required'        => 'не указана серия паспорта пользователя',
            'passport_series.digits'          => 'неверный формат серии паспорта пользователя',
            'passport_number.required'        => 'не указан номер паспорта пользователя',
            'passport_number.digits'          => 'неверный формат номера паспорта пользователя',
            'passport_code.required'          => 'не указан код подразделения паспорта пользователя',
            'passport_code.fms_code'          => 'неверный формат кода подразделения паспорта пользователя',
            'passport_issuer.required'        => 'не указано кем выдан паспорт пользователя',
            'passport_issue_date.required'    => 'не указана дата выдачи паспорта пользователя',
            'passport_issue_date.date_format' => 'неверный формат даты выдачи паспорта пользователя',
            'snils.required'                  => 'не указан номер СНИЛС пользователя',
            'snils.snils'                     => 'неверный формат СНИЛС пользователя',
            'address.required'                => 'не указан адрес пользователя',
            'address_region.required'         => 'не выбран регион пользователя',
            'address_city.required'           => 'не указан город пользователя',
            'address_street.required'         => 'не указана улица пользователя',
            'address_house.required'          => 'не указан дом пользователя',
            'job_category_id.required'        => 'не указана категория работы пользователя',
        ];

        if (isset($userData['snils'])) {
            $user_data['snils'] = preg_replace("/\D/", "", trim($userData['snils']));
        }
        return Validator::make($userData, $userValidateRules, $userErrorMessages);
    }
}
