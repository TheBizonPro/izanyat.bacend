<?php

namespace App\Exceptions;

use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return \Response::json([
                'error' => 'Route not found'
            ], 404);
        });

        $this->renderable(function (ModelNotFoundException $exception) {
            return \Response::json([
                'error' => 'Не найдено'
            ], 404);
        });

        $this->renderable(function (JWTException $exception) {
            return $this->authError();
        });
        $this->renderable(function (TokenInvalidException $exception) {
            return $this->authError();
        });
        $this->renderable(function (TokenExpiredException $exception) {
            return $this->authError();
        });
        $this->renderable(function (ThrottleRequestsException $exception) {
            return \Response::json([
                'error' => 'Too many requests'
            ], 429);
        });

        $this->reportable(function (Throwable $e) {
            $request = request();
            TelegramAdminBotClient::sendAdminNotification(
                TelegramAdminBotClient::createInfoMessage('500 Ошибка!', [
                    'URL' => $request->url(),
                    'Код' => $e->getCode(),
                    'Сообщение' => $e->getMessage(),
                    'Строка' => $e->getLine(),
                    'Файл' => ($e->getFile() ?? 'хз'),
                    'Запрос' => $request->method(),
                    'Класс' => get_class($e)
                ], 'ошибка')
            );

            return response()->json([
                'error' => 'Unexpected server error'
            ], 500);
        });
    }


    private function authError()
    {
        return \Response::json([
            'error' => 'Unauthenticated'
        ], 401);
    }
}
