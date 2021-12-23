<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanManageRoleMiddleware
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
        $companyId = intval($request->route('company_id'));

        if ($companyId !== $request->user()->company_id)
            return \Response::json([
                'error' => 'Доступ запрещен'
            ]);

        return $next($request);
    }
}
