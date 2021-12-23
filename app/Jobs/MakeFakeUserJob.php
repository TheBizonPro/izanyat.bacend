<?php

namespace App\Jobs;

use App\Exceptions\UserRegistrationExeption;
use App\Models\User;
use App\Services\Telegram\TelegramAdminBotClient;
use App\Services\UsersService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use function Symfony\Component\Translation\t;

class MakeFakeUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $userType;

    /**
     * @param string $userType
     */
    public function __construct(string $userType)
    {
        $this->userType = $userType;
    }


    /**
     * @throws UserRegistrationExeption
     */
    public function handle(UsersService $usersService)
    {
        $registrationType = 'contractor';
        $isContractor = true;

        if ($this->userType === 'rik' || $this->userType === 'sport-it') {
            $isContractor = false;
            $registrationType = 'client';
        }

        $userData = [];

        $registerUserData = [
            'registration_type' => $registrationType
        ];

        if ($isContractor) {
            $userData = $this->getNPDs()[$this->userType] ?? throw new \Exception('Не найден тип фейкового пользователя');
        } else {
            $userIndex = 0;
            if ($this->userType === 'sport-it')
                $userIndex = 1;
            $userData = $this->getFakeCompanyUser()[$userIndex];

            $company = $this->getCompanies()[$this->userType] ?? throw new \Exception('Компания не найдена');

            $registerUserData['company'] = $company;
        }

        $registerUserData['user'] = $userData;

        $user = new User;
        $user->phone = $userData['phone'];
        $user->password = \Hash::make('123456');
        $user->save();

        try {
            $usersService->registerUser($registerUserData, $user);
        } catch (UserRegistrationExeption $exception) {
            $user->delete();
            TelegramAdminBotClient::sendAdminNotification(
              TelegramAdminBotClient::createInfoMessage('fake reg errors', $exception->getErrors(), 'register')
            );
        }
    }

    public function getNPDs()
    {
        return [
            'rivok' => [
                'email' => 'ew.mwriter@gmail.com',
                'phone' => '77799999999',
                'phone_code' => NULL,
                'phone_code_sent_at' => NULL,
                'phone_confirmed' => 1,
                'password' => \Hash::make(123456),
                'remember_token' => NULL,
                'inn' => '281995847308',
                'firstname' => 'Одинокий',
                'lastname' => 'Рывок',
                'patronymic' => 'Плутархович',
                'sex' => 'm',
                'birth_place' => 'Москва',
                'birth_date' => '09.04.1923',
                'passport_series' => '4036',
                'passport_number' => '172268',
                'passport_code' => '157-183',
                'passport_issuer' => 'ОФМС РОССИИ',
                'passport_issue_date' => '09.04.1945',
                'snils' => '673-768-125 45',
                'is_identified' => '1',
                'signme_id' => NULL,
                'signme_code' => NULL,
                'taxpayer_registred_as_npd' => 1,
                'taxpayer_binded_to_platform' => 0,
                'taxpayer_income_limit_not_exceeded' => NULL,
                'taxpayer_bind_id' => NULL,
                'rating' => 1,
                'about' => 'Я Одинокий Рывок Плутархович',
                'is_administrator' => 0,
                'is_client' => 0,
                'is_selfemployed' => 1,
                'job_category_id' => 3,
//                'company_id' => NULL,
                'address_region' => '12',
                'address_city' => 'Москва',
                'address_street' => 'Тверская',
                'address_house' => '1',
                'address_building' => '1',
                'address_flat' => '1',
                'must_have_task_documents' => 1,
            ],
            'bich' => [
              'email' => 'ew.mwriter1@gmail.com',
              'phone' => '79287777777',
              'phone_code' => NULL,
              'phone_code_sent_at' => NULL,
              'phone_confirmed' => 1,
              'password' => \Hash::make(123456),
              'remember_token' => NULL,
              'inn' => '578582583922',
              'firstname' => 'Бич',
              'lastname' => 'Торжественный',
              'patronymic' => 'Ницшеевич',
              'sex' => 'm',
              'birth_place' => 'Москва',
              'birth_date' => '19.04.1923',
              'passport_series' => '3039',
              'passport_number' => '843167',
              'passport_code' => '157-183',
              'passport_issuer' => 'ОФМС РОССИИ',
              'passport_issue_date' => '01.01.2000',
              'snils' => '939-214-696 41',
              'is_identified' => '1',
              'signme_id' => NULL,
              'signme_code' => NULL,
              'taxpayer_registred_as_npd' => 1,
              'taxpayer_binded_to_platform' => 0,
              'taxpayer_income_limit_not_exceeded' => NULL,
              'taxpayer_bind_id' => NULL,
              'rating' => 1,
              'about' => 'Я Торжественный Бич Ницшевич',
              'is_administrator' => 0,
              'is_client' => 0,
              'is_selfemployed' => 1,
              'job_category_id' => 3,
//              'company_id' => NULL,
              'address_region' => '12',
              'address_city' => 'Москва',
              'address_street' => 'Тверская',
              'address_house' => '1',
              'address_building' => '1',
              'address_flat' => '1',
              'must_have_task_documents' => 1,
            ]
        ];
    }

    public function getCompanies()
    {
        return [
            'rik' => [
                'signer_user_id' => NULL,
                'balance' => 100000,
                'name' => 'ООО "РИК"',
                'full_name' => 'Общество с ограниченной ответственностью "Рога и Копыта"',
                'address_region' => 1,
                'address_city' => 'Москва',
                'legal_address' => 'г Москва, Центросоюзный пер, д 21А стр 30, пом 2 комн 9',
                'fact_address' => 'г Москва, Центросоюзный пер, д 21А стр 30, пом 2 комн 9',
                'inn' => '7703399102',
                'ogrn' => '1157746916425',
                'okpo' => '51038919',
                'kpp' => '770101001',
                'email' => 'asdsdf@mail.ru',
                'about' => NULL,
                'phone' => '78005553535',
                'signme_id' => NULL,
            ],
            'sport-it' => [
                'signer_user_id' => NULL,
                'balance' => 120000,
                'name' => 'ООО "СПОРТ-АЙТИ"',
                'full_name' => 'Общество с ограниченной ответственностью "СПОРТ-АЙТИ"',
                'address_region' => 'Московская Область',
                'address_city' => 'Подольск',
                'legal_address' => 'Московская обл, г Подольск, поселок Стрелковской фабрики, ул Спортивная, д 4, пом 1',
                'fact_address' => 'Московская обл, г Подольск, поселок Стрелковской фабрики, ул Спортивная, д 4, пом 1',
                'inn' => '5074067672',
                'ogrn' => '1215000001810',
                'okpo' => '29633335',
                'kpp' => '507401001',
                'email' => 'sportit@sportit.ru',
                'about' => NULL,
                'phone' => '79999999999',
                'signme_id' => NULL,
            ]
        ];
    }

    public function getFakeCompanyUser()
    {
        return [
            [
                'email' => 'ew.mwriter11@gmail.com',
                'phone' => '79999999997',
                'phone_code' => NULL,
                'phone_code_sent_at' => NULL,
                'phone_confirmed' => 1,
                'password' => \Hash::make(123456),
                'remember_token' => NULL,
                'inn' => '616936703356',
                'firstname' => 'Рог',
                'lastname' => 'Рогов',
                'patronymic' => 'Рогович',
                'sex' => 'm',
                'birth_place' => 'Москва',
                'birth_date' => '09.04.1923',
                'passport_series' => '4036',
                'passport_number' => '172268',
                'passport_code' => '157-183',
                'passport_issuer' => 'ОФМС РОССИИ',
                'passport_issue_date' => '09.04.1945',
                'snils' => '619-539-432 24',
                'is_identified' => '1',
                'signme_id' => NULL,
                'signme_code' => NULL,
                'taxpayer_registred_as_npd' => 0,
                'taxpayer_binded_to_platform' => 0,
                'taxpayer_income_limit_not_exceeded' => NULL,
                'taxpayer_bind_id' => NULL,
                'rating' => 1,
                'about' => 'Я ROG',
                'is_administrator' => 0,
                'is_client' => 1,
                'is_selfemployed' => 0,
                'job_category_id' => 3,
                'company_id' => NULL,
                'address_region' => '12',
                'address_city' => 'Москва',
                'address_street' => 'Тверская',
                'address_house' => '1',
                'address_building' => '1',
                'address_flat' => '1',
                'must_have_task_documents' => 1,
            ],
            [
                'email' => 'ew.mwriter121@gmail.com',
                'phone' => '79999999977',
                'phone_code' => NULL,
                'phone_code_sent_at' => NULL,
                'phone_confirmed' => 1,
                'password' => \Hash::make(123456),
                'remember_token' => NULL,
                'inn' => '761946124787',
                'firstname' => 'Василий',
                'lastname' => 'Главновский',
                'patronymic' => 'Петрович',
                'sex' => 'm',
                'birth_place' => 'Москва',
                'birth_date' => '09.04.1923',
                'passport_series' => '4036',
                'passport_number' => '172268',
                'passport_code' => '157-183',
                'passport_issuer' => 'ОФМС РОССИИ',
                'passport_issue_date' => '09.04.1945',
                'snils' => '137-241-792 58',
                'is_identified' => '1',
                'signme_id' => NULL,
                'signme_code' => NULL,
                'taxpayer_registred_as_npd' => 0,
                'taxpayer_binded_to_platform' => 0,
                'taxpayer_income_limit_not_exceeded' => NULL,
                'taxpayer_bind_id' => NULL,
                'rating' => 1,
                'about' => 'Я Vasek',
                'is_administrator' => 0,
                'is_client' => 1,
                'is_selfemployed' => 0,
                'job_category_id' => 3,
                'company_id' => NULL,
                'address_region' => '12',
                'address_city' => 'Москва',
                'address_street' => 'Тверская',
                'address_house' => '1',
                'address_building' => '1',
                'address_flat' => '1',
                'must_have_task_documents' => 1,
            ]
        ];
    }
}
