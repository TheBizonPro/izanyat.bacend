<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAccessToProjectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $requester = $request->user();
        $projectId = intval($request->route('project_id'));

        if (!$requester->hasAccessToProject($projectId))
            return \Response::json([
                'error' => 'Не разрешено'
            ], 403);

        return $next($request);
    }
}
