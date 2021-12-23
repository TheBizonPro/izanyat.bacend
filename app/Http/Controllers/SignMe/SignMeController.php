<?php

namespace App\Http\Controllers\SignMe;

use App\Http\Controllers\Controller;
use App\Jobs\SignMe\RegisterSignMeUserAndCompanyJob;
use App\Jobs\SignMe\SignMeRegistration;
use App\Models\SignMeUserState;
use App\Models\User;
use App\Services\SignMeService;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;
use Psr\Http\Client\ClientExceptionInterface;

class SignMeController extends Controller
{
    protected SignMeService $signMeService;

    /**
     * @param SignMeService $signMeService
     */
    public function __construct(SignMeService $signMeService)
    {
        $this->signMeService = $signMeService;
    }

    public function updateIdentification(Request $request)
    {
        $user = $request->user();
        $isIdentified = $this->signMeService->setUser($user)->isIdentified();

        if ($isIdentified) {
            $user->update([
                'is_identified' => true
            ]);

            $user->signMeState->update([
                'status' => SignMeUserState::STATUS_APPROVED
            ]);

            return [
                'message' => 'Пользователь успешно подтвержден',
                'user' => $user->toArray()
            ];
        }

        return [
            'message' => 'Пользователь не идентифицирован в SignMe'
        ];
    }

    public function registerCompanyUser(Request $request)
    {
        $user = $request->user();

        $this->signMeService->setUser($user);

        $missingFields = $this->signMeService->getMissingUserDataForSignMe();

        if (
            count($this->signMeService->getMissingUserDataForSignMe()) > 0
        ) {
            return \Response::json([
                'error' => 'Не хватает данных для регистрации пользователя',
                'fields' => $missingFields
            ], 400);
        }

        RegisterSignMeUserAndCompanyJob::dispatch($user);

        return [
            'message' => 'Начата регистрация пользователя в SignMe'
        ];
    }

    public function getSignMeState(Request $request)
    {
        return [
            'signme_state' => $request->user()->signMeState
        ];
    }

    public function registerContractor(Request $request)
    {
        $user = $request->user();
        $signMeState = $user->signMeState;

        if (isset($signMeState) and $signMeState->status === SignMeUserState::STATUS_REQUEST_IN_PROGRESS)
            return \Response::json([
                'error' => 'Запрос на регистрацию уже отправлен, ожидайте'
            ], 400);

        $this->signMeService->setUser($user);
        $missingFields = $this->signMeService->getMissingUserDataForSignMe();

        if (
            count($this->signMeService->getMissingUserDataForSignMe()) > 0
        ) {
            return \Response::json([
                'error' => 'Заполните данные для регистрации пользователя',
                'fields' => $missingFields
            ], 400);
        }

        SignMeRegistration::dispatchSync($user);

        return [
            'message' => 'Регистрация пользователя начата'
        ];
    }

    public function certData(Request $request)
    {
        $user = \Auth::user();

        $this->signMeService->setUser($user);

        $certData = json_decode($this->signMeService->certInfo());
        if (is_array($certData)) {
            $certData = $certData[0];
        }
        return response()->json([
            'message' => 'Данные о сертификате получены',
            'cert' => $certData
        ]);
    }
}
