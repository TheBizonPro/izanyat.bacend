<?php

namespace App\Http\Controllers\Task;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use App\Http\Controllers\Controller;
use App\Jobs\StartTaskPaymentProcessJob;
use App\Services\LogsService;
use App\Services\NotificationsService;
use App\Services\TasksService;
use Illuminate\Http\Request;

use App\Http\Resources\TaskResource;
use App\Http\Resources\OfferResource;

use DB;
use Auth;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Task;
use App\Models\Offer;
use App\Models\Notification;

use Illuminate\Support\Facades\Validator;

use App\Jobs\SendNotificationJob;
use App\Models\Project;
use Log;

class TaskController extends Controller
{
    protected TasksService $tasksService;
    protected NotificationsService $notificationsService;
    protected LogsService $logsService;

    /**
     * @param TasksService $tasksService
     * @param NotificationsService $notificationsService
     * @param LogsService $logsService
     */
    public function __construct(TasksService $tasksService, NotificationsService $notificationsService, LogsService $logsService)
    {
        $this->tasksService = $tasksService;
        $this->notificationsService = $notificationsService;
        $this->logsService = $logsService;
    }


    public function get(Request $request)
    {
        $me = Auth::user();
        $task = Task::where('id', '=', $request->task_id)->firstOrFail();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные загружены',
                'task' => new TaskResource($task)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function getAll(Request $request)
    {
        $user = Auth::user();

        $requestParams = $request->all();
        $withCompanies = ($requestParams['withCompanies'] ?? 0) == 1;

        $tasksQuery = Task::whereStatus('new');

        if ($withCompanies)
            $tasksQuery->with('project.company');

        return [
            'tasks' => $tasksQuery
                ->leftJoin('offers', 'tasks.id', '=', 'offers.task_id')
                ->orWhere(DB::raw("offers.user_id = {$user->id}"))
                ->select(DB::raw('tasks.*,offers.id as my_offer_id'))
                ->get()
        ];
    }

    public function getUserTasks(int $userId, Request $request)
    {
        //TODO переделать на мидлвейр
        if ($request->user()->id !== $userId) {
            return response()->json([
                'error' => 'Not allowed'
            ], 403);
        }

        $requestParams = $request->all();
        $withCompanies = ($requestParams['withCompanies'] ?? 0) == 1;

        $taskQuery = Task::whereUserId($userId);

        if ($withCompanies)
            $taskQuery->with('project.company');

        return [
            'tasks' => $taskQuery->get()
        ];
    }


    public function delete(Request $request)
    {
        $me = Auth::user();
        $task = Task::where('id', '=', $request->task_id)->first();

        if ($task == null) {
            return abort(404);
        }

        $this->logsService->userLog('Задача "' . $task->name . '" удалена', $me->id, $task->toArray());

        $task->delete();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача удалена',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function copy(Request $request)
    {
        $me = Auth::user();
        $task = Task::where('id', '=', $request->task_id)->first();

        if ($task == null) {
            return abort(404);
        }

        $taskCopy = new Task;
        $taskCopy->project_id = $task->project_id;
        $taskCopy->name = $task->name . ' (копия)';
        $taskCopy->status = 'new';
        $taskCopy->description = $task->description;
        $taskCopy->address = $task->address;
        $taskCopy->user_id = null;
        $taskCopy->job_category_id = $task->job_category_id;
        $taskCopy->date_from = $task->date_from;
        $taskCopy->date_till = $task->date_till;
        $taskCopy->sum = $task->sum;
        $taskCopy->save();

        $this->logsService->userLog('Задача "' . $task->name . '" скопирована', $me->id, [
            'new_task' => $taskCopy->toArray(),
            'old_task' => $task->toArray()
        ]);

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача скопирована',
                'task' => new TaskResource($taskCopy)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function offers(Request $request)
    {
        $me = Auth::user();
        $task = Task::findOrFail($request->task_id);

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные загружены',
                'offers' => OfferResource::collection($task->offers)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }




    public function save(Request $request)
    {
        $me = Auth::user();

        if ($request->task_id == 'new') {
            $task = new Task;
            $task->project_id = $request->project_id;
        } else {
            $task = Task::where('id', '=', $request->task_id)->firstOrFail();
        }

        $rules = [
            'name' => ['required', 'max:255'],
            'description' => ['required'],
            'address' => ['required'],
            'job_category_id' => ['required'],
            'date_from' => ['required'],
            'date_till' => ['required'],
            'sum' => ['required', 'integer', 'max:1000000', 'min:0'],
            'is_sum_confirmed' => ['boolean']
        ];

        $errors = [
            'name.required' => 'Не указано название задачи',
            'name.max' => 'Название задачи не может превышать 255 символов',
            'description.required' => 'Не указано описание работ',
            'address.required' => 'Не указан адрес работ',
            'job_category_id.required' => 'Не выбрана категория работ',
            'date_from.required' => 'Не указана дата начала работ',
            'date_till.required' => 'Не указана дата окончания работ',
            'sum.required' => 'Не указана сумма',
            'sum.max' => 'Максимальная сумма задачи не должна превышать 1 000 000 руб',
            'sum.min' => 'Сумма должна быть больше нуля',
        ];


        $validator = Validator::make($request->all(), $rules, $errors);

        $validationFailed = $validator->fails();
        $errorMessages = $validator->messages()->all();


        $dateFrom = Carbon::parse($request->date_from);
        $dateTill = Carbon::parse($request->date_till);

        if ($dateTill->isBefore($dateFrom)) {
            $validationFailed = true;

            $errorMessages[] = 'Дата окончания не может быть раньше даты начала';
        }

        if ($validationFailed) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Некоторые поля заполненые неверно',
                    'errors' => $errorMessages
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $task->fill($request->all());
        $task->save();
        $task->refresh();

        $this->logsService->userLog('Задача "' . $task->name . '" обновлена', $me->id, $task->toArray());

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача сохранена!',
                'task' => new TaskResource($task)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function acceptPrice(Request $request)
    {
        $me = Auth::user();
        $task = Task::findOrFail($request->task_id);

        $sum = $request->sum;

        if (!$sum) {
            return response()->json([
                'error' => 'Введите сумму'
            ], 403);
        }
        if ($task->company()->id === $me->company_id) {
            return response()->json([
                'error' => 'Not allowed'
            ], 403);
        }

        $task->sum = $sum;
        $task->is_sum_confirmed = true;

        $task->save();


        $project = Project::find($task->project_id);

        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatchSync(
                $user,
                'Согласование оплаты',
                "Исполнитель согласился на сумму по задаче <a href='/project/{$task->project->id}/tasks/{$task->id}'>#{$task->id} {$task->name}</a>"
            );
        });

        SendNotificationJob::dispatchSync(
            $task->company()->signerUser,
            'Согласование оплаты',
            "Исполнитель согласился на сумму по задаче <a href='/project/{$task->project->id}/tasks/{$task->id}'>#{$task->id} {$task->name}</a>"
        );

        return response()->json([
            'title' => 'Успешно',
            'message' => 'Цена согласована',
            'task' => new TaskResource($task)
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function acceptDeny(Request $request)
    {
        $me = Auth::user();
        $task = Task::findOrFail($request->task_id);

        if ($task->company()->id === $me->company_id) {
            return response()->json([
                'error' => 'Not allowed'
            ], 403);
        }

        $project = Project::find($task->project_id);

        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatch(
                $user,
                'Задача принята к оплате',
                "Задача <a href='/project/{$task->project->id}/tasks/{$task->id}'>{$task->name}</a> принята к оплате"
            );
        });

        SendNotificationJob::dispatchSync(
            $task->company()->signerUser,
            'Согласование оплаты',
            "Исполнитель отклонил сумму по задаче <a href='/project/{$task->project->id}/tasks/{$task->id}'>#{$task->id} {$task->name}</a>"
        );

        return response()->json([
            'title' => 'Успешно',
            'message' => 'Цена отклонена',
            'task' => new TaskResource($task)
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function confirm(Request $request)
    {
        $me = Auth::user();
        $task = Task::findOrFail($request->task_id);
        $sum = $request->sum;

        if ($task->company()->id !== $me->company_id) {
            return response()->json([
                'error' => 'Not allowed'
            ], 403);
        }

        if (!$sum) {
            return response()->json([
                'error' => 'Введите сумму'
            ], 403);
        }
        try {
            SendNotificationJob::dispatchSync(
                $task->user,
                'Согласование оплаты',
                "Заказчик <a href='/company/{$task->company()->id}'>{$task->company()->name}</a> отправил сумму {$sum}₽ на согласование по задаче <a href='/contractor/tasks/my/{$task->id}'>#{$task->id} {$task->name}</a> </br>
                <div><button class='btn btn-success btn-confirm-price me-2' data-task-id='{$task->id}' data-sum='{$sum}'>Принять</button> <button class='btn btn-error btn-deny-price' data-task-id='{$task->id}'>Отклонить</button></div>"
            );
        } catch (\Throwable $th) {
            Log::channel('debug')->debug($th->getMessage());
        }

        return response()->json([
            'title' => 'Успешно',
            'message' => 'Цена отправлена на согласование',
            'task' => new TaskResource($task)
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function pay(Request $request)
    {
        $me = Auth::user();
        $task = Task::findOrFail($request->task_id);

        if ($task->company()->id !== $me->company_id) {
            return response()->json([
                'error' => 'Not allowed'
            ], 403);
        }

        $this->logsService->userLog('Попытка оплатить задачу "' . $task->name . '"', $me->id, $task->toArray());

        $taskCanBePayedResult = $this->tasksService->taskCanBePayed($task);

        if (!$taskCanBePayedResult['result']) {
            $this->logsService->userLog('Задача "' . $task->name . '" не может быть оплачена', $me->id, [
                'task' => $task->toArray(),
                $taskCanBePayedResult
            ]);

            return response()
                ->json([
                    'title' => 'Ошибка выплаты',
                    'message' =>  $taskCanBePayedResult['reasons']
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        try {
            StartTaskPaymentProcessJob::dispatchSync($task);
            $this->logsService->userLog('Задача "' . $task->name . '" успешно принята к оплате', $me->id, $task->toArray());
        } catch (\Throwable $e) {
            $this->logsService->userLog('Выплата по задаче "' . $task->name . '" не удалась', $me->id, [
                'task' => $task->toArray(),
                'error' => $e->getMessage()
            ]);

            return response()
                ->json([
                    'title' => 'Ошибка выплаты',
                    'message' => $e->getMessage()
                ], 406, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Принято в работу',
                'offers' => OfferResource::collection($task->offers)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function return(Request $request)
    {
        $me = Auth::user();
        $task = Task::where('id', '=', $request->task_id)->first();

        if ($task == null) {
            return abort(404);
        }

        $task->status = 'work';
        $task->save();

        SendNotificationJob::dispatch($task->user, 'Задача возвращена на доработку', "Задача <a href='/contractor/tasks/my/{$task->id}'>$task->name</a> возвращена на доработку ");
        $this->logsService->userLog('Задача "' . $task->name . '" возвращена исполнителю на доработку', $me->id, $task->toArray());

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача возвращена на доработку',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    /**
     * проверить наличие оффера от смз
     */
    public function myOffer(Request $request)
    {
        $me = Auth::user();

        $task = Task::where('id', '=', $request->task_id)->first();

        if ($task == null) {
            return abort(404);
        }

        $offer = Offer::where('task_id', '=', $task->id)->where('user_id', '=', $me->id)->first();

        if ($offer != null) {
            $offer = new OfferResource($offer);
        }

        $this->logsService->userLog('Пользователь откликнулся на задачу', $me->id, $offer?->toArray() ?? []);

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Предложение создано!',
                'offer' => $offer
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Завершить задачу СМЗ
     */
    public function complete(Request $request)
    {
        $me = Auth::user();

        $task = Task::findOrFail($request->task_id);

        $this->logsService->userLog('Попытка сдать задачу', $me->id, $task->toArray());

        if (!$this->tasksService->isTaskCanBeDone($task)) {
            $this->logsService->userLog('Пользователь не смог сдать задачу, выполнения задачи необходимо подписать договор и заказ-наряд', $me->id, $task->toArray());

            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Для выполнения задачи необходимо подписать договор и заказ-наряд',
                    'task' => new TaskResource($task)
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        try {
            $this->tasksService->taskDone($task);
            $this->logsService->userLog('Пользователь успешно сдал задачу', $me->id, $task->toArray());
        } catch (\Exception $e) {
            $this->logsService->userLog('Ошибка при сдаче задачи самозанятым', $me->id, [
                'error' => $e->getMessage(),
                'task' => $task->toArray()
            ]);
        }

        $task->refresh();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача отправлена на проверку заказчику',
                'task' => new TaskResource($task)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function clientComplete(Request $request)
    {
        $me = Auth::user();

        $task = Task::findOrFail($request->task_id);

        if (!$task->is_sum_confirmed) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Для принятия задачи необходимо согласовать цену',
                    'task' => new TaskResource($task)
                ], 406, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);;
        }

        $this->logsService->userLog('Попытка принять задачу', $me->id, $task->toArray());

        try {
            $this->tasksService->clientTaskDone($task);
            $this->logsService->userLog('Пользователь успешно принял задачу задачу', $me->id, $task->toArray());
        } catch (\Exception $e) {
            $this->logsService->userLog('Ошибка при принятия задачи', $me->id, [
                'error' => $e->getMessage(),
                'task' => $task->toArray()
            ]);
        }

        $task->refresh();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Задача проверена',
                'task' => new TaskResource($task)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    /**
     * Отказ от задачи
     */
    public function refuse(Request $request)
    {
        $me = Auth::user();

        $task = Task::where('id', '=', $request->task_id)->first();

        if ($task == null) {
            return abort(404);
        }

        Offer::where('user_id', '=', $task->user_id)->where('task_id', '=', $task->id)->delete();

        $task->status = 'new';
        $task->user_id = null;
        $task->save();

        $project = Project::find($task->project_id);

        $this->logsService->userLog('Пользователь отказался от выполнения задачи', $me->id, $task->toArray());

        try {
            $notification = new Notification;
            $notification->user_id = $me->id;
            $notification->is_readed = false;
            $notification->from = 'Платформа';
            $notification->subject = 'Отказ от выполнения задачи «' . $task->name . '»';
            $notification->text = 'Вы отказались от выполнения задачи «' . $task->name . '» от заказчика ' . $task->project->company->name;
            $notification->plain_text = 'Вы отказались от выполнения задачи «' . $task->name . '» от заказчика ' . $task->project->company->name;
            $notification->action = [
                'type' => NotificationTypes::ENTITY,
                'entity' => NotificationEntities::TASK,
                'entity_id' => $task->id
            ];
            $notification->save();
            $this->logsService->userLog('Отправка уведомления пользователю о том, что он отказался от выполнения задачи', $me->id, $task->toArray());
        } catch (\Throwable $e) {
        }

        try {
            $notification = new Notification;
            $notification->user_id = $task->project->company->signer_user_id;
            $notification->is_readed = false;
            $notification->from = 'Платформа';
            $notification->subject = "Исполнитель отказался от выполнения задачи «" . $task->name . "»";
            $notification->text = "Исполнитель {$me->name} отказался от исполнения задачи «<a href='/project/{$task->project->id}/tasks/{$task->id}'>$task->name</a>»";
            $notification->plain_text = "Исполнитель {$me->name} отказался от исполнения задачи «{$task->name}»";
            $notification->action = [
                'type' => NotificationTypes::ENTITY,
                'entity' => NotificationEntities::TASK,
                'entity_id' => $task->id
            ];
            $notification->save();

            $project->users->each(function ($user) use ($task, $me) {
                try {
                    $notification = new Notification;
                    $notification->user_id = $user->id;
                    $notification->is_readed = false;
                    $notification->from = 'Платформа';
                    $notification->subject = "Исполнитель отказался от выполнения задачи «" . $task->name . "»";
                    $notification->text = "Исполнитель {$me->name} отказался от исполнения задачи «<a href='/project/{$task->project->id}/tasks/{$task->id}'>$task->name</a>»";
                    $notification->plain_text = "Исполнитель {$me->name} отказался от исполнения задачи «{$task->name}»";
                    $notification->action = [
                        'type' => NotificationTypes::ENTITY,
                        'entity' => NotificationEntities::TASK,
                        'entity_id' => $task->id
                    ];
                    $notification->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });
            $this->logsService->userLog('Отправка уведомления компании о том, что он пользователь от выполнения задачи', $task->project->company->signer_user_id, $task->toArray());
        } catch (\Throwable $e) {
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы отказались от выполнения задачи',
                'task' => new TaskResource($task)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * сделать оффера от смз
     */
    public function makeOffer(Request $request)
    {
        $me = Auth::user();

        $task = Task::where('id', '=', $request->task_id)->first();
        $currentOffer = Offer::whereUserId($me->id)->whereTaskId($task->id)->first();

        if ($currentOffer !== null) {
            return \Response::json([
                'error' => 'Вы уже оставили отклик на эту задачу'
            ]);
        }

        if ($task == null) {
            return abort(404);
        }

        $offer = new Offer;
        $offer->user_id = $me->id;
        $offer->task_id = $task->id;
        $offer->project_id = $task->project_id;
        $offer->save();

        $this->logsService->userLog('Пользователь откликнулся на задачу', $me->id, $task->toArray());

        $project = Project::find($task->project_id);
        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatch(
                $user,
                'Новый отклик на задачу',
                "По задаче <a href='/project/{$task->project->id}/tasks/{$task->id}'>$task->name</a> появился отклик"
            );
        });

        SendNotificationJob::dispatch(
            $task->project->company->signerUser,
            'Новый отклик на задачу',
            "По задаче <a href='/project/{$task->project->id}/tasks/{$task->id}'>$task->name</a> появился отклик"
        );

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Предложение создано!',
                'offer' => new OfferResource($offer)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function inviteUser(Request $request)
    {
        $me = Auth::user();


        $task = Task::find($request->task_id);
        $user = User::find($request->user_id);

        $this->notificationsService->createInvitationNotification($task, $user);

        $this->logsService->userLog('Самозанятый приглашен в задачу', $me->id, [
            'user' => $user->toArray(),
            'task' => $task->toArray()
        ]);

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Приглашение отправлено!',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
