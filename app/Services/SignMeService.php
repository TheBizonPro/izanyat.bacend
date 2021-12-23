<?php

namespace App\Services;

use App\Exceptions\NotEnoughUserDataForSignMe;
use App\Jobs\SendNotificationJob;
use App\Logging\LogsHelper;
use App\Models\Document;
use App\Models\SignMeUserState;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\Pure;
use PackFactory\SignMe\DTO\CompanyDTO;
use PackFactory\SignMe\DTO\RegistrationRequestDTO;
use PackFactory\SignMe\DTO\UserDTO;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;
use Psr\Http\Client\ClientExceptionInterface;

class SignMeService
{

    protected SignMe $signMe;
    protected LogsService $logsService;
    protected User $user;

    /**
     * @param SignMe $signMe
     */
    #[Pure]
    public function __construct(SignMe $signMe)
    {
        $this->signMe = $signMe;
        $this->logsService = LogsHelper::createFsLogger();
    }

    public function userInfo()
    {
        if (!isset($this->user))
            throw new \Exception('Не установлен пользователь');
        try {
            $response = $this->signMe->getUserInfo([
                'phone' => "+{$this->user->phone}"
            ]);
            return $response;
        } catch (\Throwable $e) {
            $this->logsService->signmeLog('Выброшено исключение при получении данных пользователя', $this->user->id, [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function certInfo()
    {
        if (!isset($this->user))
            throw new \Exception('Не установлен пользователь');
        try {
            $data = [
                'snils' => $this->user->snils,
            ];

            if ($this->user->is_client) {
                $data['inn'] = $this->user->company->inn;
            }

            $response = $this->signMe->certInfo($data);
            return $response;
        } catch (\Throwable $e) {
            $this->logsService->signmeLog('Выброшено исключение при получении данных о сертификате пользователя', $this->user->id, [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * @param User $user
     * @return SignMeService
     */
    public function setUser(User $user): SignMeService
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws \Exception|ClientExceptionInterface
     */
    public function registerUser(): RegistrationRequestDTO
    {
        if (!isset($this->user))
            throw new \Exception('Не установлен пользователь');

        try {
            $this->removeExistingUserRegistrationRequests();

            $userDTO = $this->userDTO();
            $response = $this->signMe->createUser($userDTO);

            $this->saveSignMeRegistrationData($response);

            if (isset($response->pdf, $response->compdf))
                $this->saveAnketa($response->pdf, $response->compdf);
        } catch (\Exception | ClientExceptionInterface $e) {
            $this->logsService->signmeLog('Выброшено исключение при регистрации пользователя', $this->user->id, [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }

        return $response;
    }

    /**
     * @throws \Exception
     * @throws ClientExceptionInterface
     */
    public function registerUserAndCompany()
    {
        if (!isset($this->user) || !isset($this->user->company))
            throw new \Exception('Не установлен пользователь или компания');

        try {
            $this->removeExistingCompanyRegistrationRequests();

            $userDTO = $this->userDTO();
            $companyDTO = $this->companyDTO();

            $response = $this->signMe->createUserAndCompany($userDTO, $companyDTO);

            $this->saveSignMeRegistrationData($response);

            if (isset($response->pdf))
                $this->saveAnketa($response->pdf, $response->compdf);
        } catch (\Exception | ClientExceptionInterface $e) {
            $this->logsService->signmeLog('Выброшено исключение при удалении заявки', $this->user->id, [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws SignMeResponseException
     */
    public function attachUserToCompany(): bool|string
    {
        try {
            $response = $this->signMe->addStaff(
                $this->user->forSignMe('phone'),
                $this->user->company->forSignMe('cogrn')
            );

            $this->logsService->signmeLog(
                'Получен ответ по привязке пользователя к компании',
                $this->user->id,
                ['response' => $response]
            );
        } catch (SignMeResponseException | ClientExceptionInterface | \Exception $e) {
            $this->logsService->signmeLog(
                'Выброшено исключение при добавлении пользователя к компании',
                $this->user->id,
                [
                    'error' => $e->getMessage()
                ]
            );

            throw $e;
        }

        return $response;
    }

    public function isIdentified()
    {
        $user = $this->user;

        $user_exists = $this->signMe->precheck([
            'inn' => $user->forSignMe('inn'),
        ]);

        if (count($user_exists) > 0) {
            $requests = [];
            foreach ($user_exists as $CheckDTO) {
                if ($CheckDTO->approved == true) {
                    $this->logsService->signmeLog('Пользователь подтвержден', $user->id);
                    return true;
                }
            }
        }

        return false;
    }

    public function getMissingUserDataForSignMe()
    {
        try {
            $this->userDTO();
            return [];
        } catch (NotEnoughUserDataForSignMe $e) {
            return $e->getFields();
        }
    }


    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    protected function removeExistingCompanyRegistrationRequests(): static
    {
        $company = $this->user->company ?? throw new \Exception('Не установлена компания пользователя');

        $companyExists = $this->signMe->precheck([
            'cinn' => $company->forSignMe('cinn'),
            'cogrn' => $company->forSignMe('cogrn'),
        ]);

        $this->logsService->signmeLog('Получили ответ по пречеку компании', $this->user->id, $companyExists);

        if ($companyExists) {
            foreach ($companyExists as $CheckDTO) {
                try {
                    $response = $this->signMe->deleteByRequestId($CheckDTO->id);

                    $this->logsService->signmeLog('Получили ответ по удалению заявки', $this->user->id, [
                        'Успех: ' => $response ? 'да' : 'нет'
                    ]);
                } catch (SignMeResponseException | ClientExceptionInterface $e) {
                    $this->logsService->signmeLog('Выброшено исключение при удалении заявки', $this->user->id, [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws SignMeResponseException
     */
    protected function removeExistingUserRegistrationRequests(): static
    {
        $user = $this->user;

        $userExists = $this->signMe->precheck([
            'phone' => $user->forSignMe('phone'),
            'snils' => $user->forSignMe('snils'),
            'email' => $user->forSignMe('email'),
        ]);

        $this->logsService->signmeLog('Получили ответ по пречеку пользователя', $user->id, $userExists);


        if (count($userExists) > 0) {
            $requests = [];
            foreach ($userExists as $CheckDTO) {
                $requests[] = $CheckDTO->id;
            }
            $requests = array_unique($requests);

            if (count($requests) > 0) {
                foreach ($requests as $request_id) {
                    $delete_status = $this->signMe->deleteByRequestId($request_id);

                    $this->logsService->signmeLog('Получили ответ по удалению заявки', $user->id, [
                        'Успех' => $delete_status ? 'Да' : 'Нет'
                    ]);
                }
            }
        }

        return $this;
    }

    protected function companyDTO(): CompanyDTO
    {
        $company = $this->user->company ?? throw new \Exception('Не установлена компания пользователя');
        $attributes = [
            'cname'    => $company->forSignMe('cname'),
            'cemail'   => $company->forSignMe('cemail'),
            'cphone'   => $company->forSignMe('cphone'),
            'ccountry' => $company->forSignMe('ccountry'),
            'cregion'  => $company->forSignMe('cregion'),
            'ccity'    => $company->forSignMe('ccity'),
            'caddr'    => $company->forSignMe('caddr'),
            'cfaddr'   => $company->forSignMe('cfaddr'),
            'cinn'     => $company->forSignMe('cinn'),
            'cogrn'    => $company->forSignMe('cogrn'),
            'ckey'     => 1,
        ];

        return CompanyDTO::createFromArray($attributes);
    }

    protected function userDTO(): UserDTO
    {
        $user = $this->user;

        $attributes = [
            'name'     => $user->forSignMe('name'),
            'surname'  => $user->forSignMe('surname'),
            'lastname' => $user->forSignMe('lastname'),
            'bdate'    => $user->forSignMe('bdate'),
            'gender'   => $user->forSignMe('gender'),
            'ps'       => $user->forSignMe('ps'),
            'pn'       => $user->forSignMe('pn'),
            'pdate'    => $user->forSignMe('pdate'),
            'issued'   => $user->forSignMe('issued'),
            'pcode'    => $user->forSignMe('pcode'),
            'snils'    => $user->forSignMe('snils'),
            'phone'    => $user->forSignMe('phone'),
            'email'    => $user->forSignMe('email'),
            'inn'      => $user->forSignMe('inn'),
            'country'  => $user->forSignMe('country'),
            'region'   => $user->forSignMe('region'),
            'city'     => $user->forSignMe('city'),
            'street'   => $user->forSignMe('street'),
            'house'    => $user->forSignMe('house'),
            'building' => $user->forSignMe('building'),
            'room'     => $user->forSignMe('room'),
            'external' => $user->forSignMe('external'),
            'delivery' => 0,
            'regtype'  => 2,
            'mobile'   => 1,
        ];

        $nullFields = [];

        foreach ($attributes as $attributeName => $attribute) {
            if ($attribute === null and $this->isNecessaryInUserDTO($attributeName))
                $nullFields[] = $attributeName;
        }

        if (count($nullFields) > 0)
            throw new NotEnoughUserDataForSignMe($nullFields);

        return UserDTO::createFromArray($attributes);
    }

    protected function isNecessaryInUserDTO($fieldName): bool
    {
        return array_search($fieldName, ['building']) === false;
    }

    protected function saveAnketa(string $pdf, string $compdf)
    {
        $path = 'users/' . $this->user->id . '/signme/signme_anketa.pdf';
        Storage::disk('cloud')->put($path, base64_decode($compdf));

        $document = new Document;
        $document->type = 'signme_anketa';
        $document->user_id = $this->user->id;
        $document->date = date('Y-m-d');
        $document->file = $path;
        $document->save();
    }

    protected function saveSignMeRegistrationData(RegistrationRequestDTO $requestDTO)
    {
        $this->user->refresh();
        $signMeState = $this->user->signMeState;

        if (isset($requestDTO->id)) {
            $signMeState->signme_id = $requestDTO->id;

            $qr = $this->signMe->getQR($requestDTO->id);

            $signMeState->signme_code = $qr;

            if (isset($qr->code)) {
                $signMeState->signme_code = $qr->code;
            }

            $signMeState->status = SignMeUserState::STATUS_AWAIT_APPROVE;

            $signMeState->save();
        }

        if ($requestDTO->cid) {
            if ($this->user->company) {
                $this->user->company->signme_id = $requestDTO->cid;
                $this->user->company->save();
            }
        }

        SendNotificationJob::dispatch(
            $this->user,
            'Использование вашей ЭЦП',
            'Здравствуйте, ' . $this->user->firstname . '!<br>Для работы в платформе вам потребуется электронная цифровая подпись (ЭЦП).  Пожалуйста, выполните следующие действия:<ul><li>Скачайте приложение SignMe в <a href="https://apps.apple.com/ru/app/sign-me/id1502259352">Appstore</a> или <a href="https://play.google.com/store/apps/details?id=me.sign">Google Play</a></li><li>Пройдите регистрацию в приложении с тем номером, с которым вы регистрировались в платформе “Я занят”</li><li>Отсканируйте в приложении QR-код (или введите его вручную: <b>' . $signMeState->signme_code . '</b>)</li></ul> <div><img src="' . config('app.url') . '/qr/' . $signMeState->signme_code . '"></div>',
            '',
            'УЦ SignMe'
        );

        return $signMeState;
    }
}
