<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MobileAuthRequest;
use App\Services\AuthService;
use App\Services\LogsService;
use App\Services\Telegram\TelegramAdminBotClient;
use App\Services\UsersService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Hash;

use App\Models\User;
use App\Jobs\SendSMS;
use JWTAuth;
use Log;

class AuthController extends Controller
{

    protected UsersService $usersService;
    protected AuthService $authService;
    protected LogsService $logsService;

    /**
     * @param UsersService $usersService
     * @param AuthService $authService
     * @param LogsService $logsService
     */
    public function __construct(UsersService $usersService, AuthService $authService, LogsService $logsService)
    {
        $this->usersService = $usersService;
        $this->authService = $authService;
        $this->logsService = $logsService;
    }


    public function mobileAuth(MobileAuthRequest $request)
    {
        $requestData = $request->validated();

        $phone = $requestData['phone_number'];
        $password = $requestData['password'];

        if (!$token = auth()->attempt(['phone' => $phone, 'password' => $password])) {
            return response()->json([
                'error' => 'Неправильный номер телефона или пароль'
            ], 403);
        }

        return [
            'token' => $token
        ];
    }

    /**
     * TODO разбить на несколько методов
     */
    public function login(Request $request)
    {
        $phone = User::formatPhone($request->phone);
        if ($phone === false) {
            return response()
                ->json([
                    'error_code' => 'invalid_phone',
                    'title' => 'Ошибка',
                    'message' => 'Неверный формат номера телефона'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $user = User::where('phone', '=', $phone)->first();

        if ($user != null) {
            $this->logsService->userLog('Попытка входа', $user->id);
            if ($user->password != null && $request->skip_password != 1) {
                // auth by password
                if ($request->password == null) {
                    return response()
                        ->json([
                            'action' => 'enter_password',
                            'title' => 'Требуется авторизация',
                            'message' => 'Необходимо ввести пароль'
                        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
                }

                if (!$token = auth()->attempt(['phone' => $phone, 'password' => $request->password])) {

                    $this->logsService->userLog('Введен неверный пароль', $user->id);
                    return response()
                        ->json([
                            'error_code' => 'invalid_password',
                            'title' => 'Ошибка',
                            'message' => 'Неверный пароль'
                        ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
                }

                Auth::login($user, true);

                $this->logsService->userLog('Введен правильный пароль, пользователь авторизован', $user->id);

                return response()
                    ->json([
                        'action' => 'redirect',
                        'title' => 'Успешно',
                        'message' => 'Вы успешно авторизовались!',
                        'token' => $token
                    ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }

            if ($request->code != null) {
                if ($user->phone_code != $request->code) {
                    $this->logsService->userLog('Введен неправильный смс-код', $user->id);

                    return response()
                        ->json([
                            'error_code' => 'invalid_code',
                            'title' => 'Ошибка',
                            'message' => 'Вы ввели неверный смс-код'
                        ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
                }
                $token = JWTAuth::fromUser($user);
                Auth::login($user, true);
                $this->logsService->userLog('Введен правильный смс-код, номер телефона подтвержден', $user->id);

                if ($request->skip_password == 1) {
                    $user->password = null;
                }

                if ($user->password == null) {
                    $action = 'set_new_password';
                    $message = 'Необходимо установить пароль для входа.';
                } else {
                    $action = 'redirect';
                    $message = 'Вы успешно авторизовались.';
                }

                return response()
                    ->json([
                        'action' => $action,
                        'title' => 'Успешно',
                        'message' => $message,
                        'token' => $token
                    ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }
        }

        // Если пользователь не создан
        if ($user == null) {
            $user = $this->usersService->createBasicUser($phone);

            $this->logsService->userLog('Пользователь создан', $user->id, $user->toArray());
        }

        $timeLeftBeforeRepeat = $user->getWaitBeforeRepeatCode();

        // Если смс-код уже был отправлен раньше
        if (!$user->canReceiveConfirmationSMS()) {

            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Повторная отправка смс-кода возможна через ' . $timeLeftBeforeRepeat . ' сек.',
                    'wait_before_repeat_code' => $timeLeftBeforeRepeat
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        // Новый смс-код
        $phoneCode = $user->setNewConfirmationCode(rand(100, 999) . '-' . rand(100, 999));

        $text = $this->usersService->generateConfirmationSMSText($phoneCode);

        try {
            SendSMS::dispatchNow($user->phone, $text);
            $this->logsService->userLog('Новый код подтверждения отправлен на номер пользователя', $user->id);
        } catch (\Throwable $e) {
            $this->logsService->userLog('Ошибка отправки смс-кода  ' . $e->getMessage(), $user->id);

            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Ошибка отправки смс-кода  ' . $e->getMessage()
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $this->logsService->userLog('Смс-код для подтверждения номера телефона успешно отправлен', $user->id);

        return response()
            ->json([
                'action' => 'enter_code',
                'title' => 'Успешно',
                'message' => 'Мы отправили вам смс-код для проверки',
                'wait_before_repeat_code' => $timeLeftBeforeRepeat
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     *
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return response()
            ->json([
                'title' => 'Сессия завершена',
                'message' => 'Вы успешно вышли из системы'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Проверка аутентификации
     */
    public function checkAuth(Request $request)
    {
        //sleep(2);
        $me = Auth::user();
        if ($me == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Необходимо войти в систему.'
                ], 401, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы аутентифицированны.',
                'me' => $me
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
