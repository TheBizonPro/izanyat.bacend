<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckIsNPD
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $me = Auth::user();
        if ($me->taxpayer_registred_as_npd != true) {
            return response()
                ->json([
                    'title' => 'Действие не разрешено',
                    'message' => 'Сперва необходимо привязаться к партнеру "ЯЗанят" в ПП НПД (приложение Мой Налог)'
                ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
        }
        return $next($request);
    }
}
