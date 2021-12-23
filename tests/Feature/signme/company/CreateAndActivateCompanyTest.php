<?php

namespace Tests\Feature;

use App\Console\Commands\SignmeActivateUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use PackFactory\SignMe\SignMe;
use Tests\TestCase;

class CreateAndActivateCompanyTest extends TestCase
{
    use DatabaseTransactions;

    public function testBasic()
    {
        $response = $this->post('/registration', [
            'registration_type' => 'client',

            'user' => [
                'email'               => 'vlad@gmail.com',
                'inn'                 => '029304564463',
                'firstname'           => 'Владислав',
                'lastname'            => 'Пупкин',
                'patronymic'          => 'Георгиевич',
                'sex'                 => 'm',
                'birth_place'         => 'Москва',
                'birth_date'          => '12.06.1976',
                'passport_series'     => '2322',
                'passport_number'     => '649113',
                'passport_code'       => '157-183',
                'passport_issuer'     => 'ОУФМС РОССИИ',
                'passport_issue_date' => '01.01.2000',
                'snils'               => '710-217-307 31',
                'address_region'      => 11,
                'address_city'        => 'Москва',
                'address_street'      => 'Тверская',
                'address_house'       => 1,
                'address_building'    => 1,
                'address_flat'        => 1,
            ],

            'company' => [
                'full_name' => 'Общество с ограниченной ответственностью "Ромашка"',
                'name' => 'ООО "Ромашка"',
                'legal_address' => 'Москва',
                'fact_address' => 'Москва',
                'inn' => '6120498685',
                'ogrn' => '3026497359897',
                'okpo' => '1234123412',
                'kpp' => '243943864',
                'phone' => '79999999818',
                'email' => 'uwu@gmail.com',
                'address_region' => 1,
                'address_city' => 'Москва',
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(User::class, [
            'inn' => '029304564463'
        ]);

        $this->assertDatabaseHas(User::class, [
            'inn' => '6120498685'
        ]);

        $user = User::whereInn('029304564463')->firstOrFail();
        $company = Company::whereInn('6120498685')->firstOrFail();

        $this->assertNotNull($user->signme_id);
        $this->assertNotNull($company->signme_id);

        $signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));

        $response = $signMe->activateUser($user->signme_id);
        $response = $signMe->activateCompany($company->signme_id);
    }
}
