<?php

namespace App\Jobs;

use App\Jobs\Documents\CreateAct;
use App\Jobs\Payout\PayoutProcess;
use App\Models\Payout;
use App\Models\Project;
use App\Models\Task;
use App\Services\LogsService;
use App\Services\TasksService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class StartTaskPaymentProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Task $task;

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }


    /**
     * @throws \Exception
     */
    public function handle(TasksService $tasksService, LogsService $logsService)
    {
        $task = $this->task;

        $taskCanBePayed = $tasksService->taskCanBePayed($task);

        if (!$taskCanBePayed['result']) {
            throw new \Exception($taskCanBePayed['reasons'][0] ?? 'Задача не может быть оплачена');
        }

        $payout = new Payout;
        $payout->project_id = $task->project_id;
        $payout->task_id = $task->id;
        $payout->user_id = $task->user_id;
        $payout->job_category_id = $task->job_category_id;
        $payout->sum = $task->sum;
        $payout->status = 'draft';
        $payout->save();

        $logsService->userLog('Создана выплата', $task->project->company->signer_user_id, $payout->toArray());

        PayoutProcess::dispatch($payout);

        $task->status = 'await_payment';
        $task->update();

        $project = Project::find($task->project_id);

        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatch(
                $user,
                'Задача принята к оплате',
                "Задача <a href='/project/{$task->project->id}/tasks/{$task->id}'>{$task->name}</a> принята к оплате"
            );
        });

        SendNotificationJob::dispatch(
            $payout->project->company->signerUser,
            'Задача принята к оплате',
            "Задача <a href='/project/{$task->project->id}/tasks/{$task->id}'>{$task->name}</a> принята к оплате"
        );

        $logsService->userLog('Отправлено уведомление о том, что начата выплата', $task->user_id, $payout->toArray());
    }
}
