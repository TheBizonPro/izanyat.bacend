<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;


class CheckIsIdentified
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
        if ($me->is_identified != true) {
            return response()
                ->json([
                    'title' => 'Действие не разрешено',
                    'message' => 'Это действие не разрешено! Необходимо завершить идентификацию через Sign.Me'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        return $next($request);
    }
}
