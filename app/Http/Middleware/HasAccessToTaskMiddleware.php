<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;

class HasAccessToTaskMiddleware
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
        $requester = $request->user();
        $taskId = intval($request->route('task_id'));
        $task = Task::findOrFail($taskId);

        if ($task->status === 'new')
            return $next($request);

        if (
            $task->user_id !== $request->user()->id
            and
            !$requester->hasAccessToProject($task->project_id)
        )
            return \Response::json(['error' => 'Не разрешено'], 403);

        return $next($request);
    }
}
