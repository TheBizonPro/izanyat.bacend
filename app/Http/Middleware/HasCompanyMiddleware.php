<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasCompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->company_id === null)
            return \Response::json([
                'error' => 'Доступ запрещен'
            ], 403);

        return $next($request);
    }
}
