<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetPermissionsCompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->company_id)
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);


        return $next($request);
    }
}
