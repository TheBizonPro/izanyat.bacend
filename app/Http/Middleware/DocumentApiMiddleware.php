<?php



namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class DocumentApiMiddleware
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
        $token = $request->header('Authorization');
        if ($token != config('document_api.token')) {
            return response()
                ->json([
                    'title' => 'Действие не разрешено',
                    'message' => 'Неверный токен'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        return $next($request);
    }
}
