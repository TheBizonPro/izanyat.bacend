<?php

namespace App\Http\Middleware;

use App\Services\Telegram\TelegramAdminBotClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Log;

class HasPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $user = $request->user();

        if (!$user->can($abilities) && !$user->isCompanyAdmin()) {
            return Response::json([
                'error' => 'Не разрешено'
            ], 403);
        }

        return $next($request);
    }
}
