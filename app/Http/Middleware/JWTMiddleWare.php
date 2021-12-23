<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;

class JWTMiddleWare
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
        if (!$request->headers->has('Authorization')) {
            return \Response::json([
                'error' => 'Unauthenticated'
            ], 401);
        }
        $user = JWTAuth::parseToken()->authenticate();
        return $next($request);
    }
}
