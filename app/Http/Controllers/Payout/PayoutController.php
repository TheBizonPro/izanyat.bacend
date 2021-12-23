<?php

namespace App\Http\Controllers\Payout;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use App\Http\Controllers\Controller;
use App\Jobs\StartTaskPaymentProcessJob;
use App\Services\PayoutsService;
use App\Services\TasksService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


use DB;
use Auth;
use Illuminate\Support\Facades\Response;
use Storage;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Payout;

use App\Models\JobCategory;
use App\Models\Notification;
use App\Models\ProjectUser;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TasksImport;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

use App\Jobs\Documents\CreateAct;
use App\Jobs\Payout\PayoutProcess;

use App\Jobs\FNS\AnnulateReceipt;

class PayoutController extends Controller
{
    protected PayoutsService $payoutsService;

    /**
     * @param PayoutsService $payoutsService
     */
    public function __construct(PayoutsService $payoutsService)
    {
        $this->payoutsService = $payoutsService;
    }


    public function payouts(Request $request)
    {
        $me = Auth::user();

        $payouts = Payout::where('user_id', $me->id)->with([
            'user',
            'project:id',
            'jobCategory:id,name'
        ])->with('project.company:id,name,inn')
            ->get()
            //TODO переделать
            ->map(function (Payout $item) {
                return [
                    'id' => $item->id,
                    'task_name' => $item->task->name,
                    'date' => "{$item->getCreatedDateAttribute()} в {$item->getCreatedTime()}",
                    'company' => $item->project->company->name,
                    'company_inn' => $item->project->company->inn,
                    'job_category' => $item->jobCategory->name,
                    'sum' => $item->sum,
                    'status' => $item->getTranslatedStatus(),
                    'status_code' => $item->status,
                    'receipt_url' => $item->getReceiptUrl(!env('FNS_OFFLINE_MODE'))
                ];
            });

        return response()->json([
            'title' => 'Успех',
            'message' => 'Выплаты получены',
            'payouts' => $payouts
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function repay(Request $request, int $payoutId)
    {
        $payout = Payout::findOrFail($payoutId);
        $user = $request->user();

        // TODO сделать мидлвейр под проверку на наличие у юзера компании
        if (!$user->company_id)
            return Response::json([
                'error' => 'Не установлена компания'
            ], 400);

        $company = $payout->project->company;

        // TODO мидлвейр
        if ($company->id !== $user->company_id)
            return Response::json([
                'error' =>  'Ай яй яй'
            ], 403);

        try {
            $this->payoutsService->repay($payout);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 400);
        }

        return [
            'message' => 'Новый платеж создан'
        ];
    }

    //TODO валидацию
    public function massPay(Request $request)
    {
        $tasksIds = $request->get('tasks');

        $tasks = Task::whereStatus('await_payment_request')->findMany($tasksIds);

        if (count($tasksIds) !== $tasks->count()) {
            return Response::json([
                'error' => 'Некоторые из выбранных задач не могут быть оплачены'
            ], 400);
        }

        $tasks->each(function (Task $task) {
            StartTaskPaymentProcessJob::dispatch($task);
        });

        return [
            'message' => 'Задачи приняты к оплате'
        ];
    }

    public function simulate(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        if ($request->hasFile('file') == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не передан файл с реестром!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $path = Storage::disk('local')->putFile('tasks_excels', $request->file('file'));

        try {
            $data = Excel::toArray(new TasksImport, $path);
        } catch (\Throwable $e) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не удалось извлечь из файла данные',
                    'exception' => $e->getMessage()
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $page0 = $data[0];
        $table = [];

        foreach ($page0 as $i => $row) {
            $row = array_values($row);
            if (Arr::get($row, 0) == null or Arr::get($row, 1) == null) {
                continue;
            }
            $table[] = [
                'job_name'            => trim(Arr::get($row, 0)),
                'inn'                 => trim(Arr::get($row, 1)),
                'job_start_date'      => trim(Arr::get($row, 2)),
                'job_finish_date'     => trim(Arr::get($row, 3)),
                'sum'                 => trim(Arr::get($row, 4))
            ];
        }

        if (count($table) == 0) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не удалось извлечь из файла данные'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $responseTasks = [
            'matched' => [],
            'conflict' => [],
            'errors' => []
        ];

        foreach ($table as $i => $tr) {
            $inn = preg_replace("/\D/", "", trim($tr['inn']));

            $tasks = Task::whereStatus('done')
                ->whereName(trim($tr['job_name']))
                ->with('user')
                ->with([
                    'user' => function ($query) use ($inn) {
                        $query->where('inn', '=', $inn);
                    }
                ])
                ->with([
                    'project' => function ($query) use ($me) {
                        $query->where('company_id', '=', $me->company_id);
                    }
                ])->get();

            if ($tasks->count() === 1) {
                $responseTasks['matched'][] = $tasks->first()->toArray();
                continue;
            }

            if ($tasks->count() > 1) {
                $responseTasks['conflict'][$i] = $tasks->toArray();
            }

            if ($tasks->count() < 1) {
                $responseTasks['errors'][] = "Задача для строки " . $i + 1 . " не найдена. Название: " . $tr['job_name'] . ", ИНН самозанятого: " . $inn;
            }
        }

        return $responseTasks;
    }


    public function download(Request $request)
    {
        $company = $request->user()?->company ?? throw new \Exception('Не установлена компания пользователя');

        $ids = $request->get('ids');

        try {
            $this->payoutsService->downloadReceipts($company, $request->get('filter') ?? [], $ids);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *
     */
    public function new(Request $request, TasksService $tasksService)
    {
        //        $me = Auth::user();
        //        if ($me->company_id == null) {
        //            return response()
        //                ->json([
        //                    'title' => 'Ошибка',
        //                    'message' => 'Не установлена компания клиента'
        //                ], 403, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
        //        }
        //
        //        // TODO валидацию
        //        $tasksToPay = $request->tasks_to_pay;
        //        $tasksIds = array_keys($tasksToPay);
        //
        //        $tasks = Task::whereProjectId($request->project_id)
        //        ->whereIn('id', $tasksIds)->get();
        //
        //        DB::beginTransaction();
        //
        //        $tasks->each(function (Task $task) use ($tasksService) {
        //            ProcessTaskPaymentJob::dispatch($task);
        //        });

        //        foreach($import_data as $row) {
        //            $hasError = false;
        //
        //            $payout = new Payout;
        //            $payout->order_id = $order->id;
        //
        //            $contractor = $project_users->where('inn', '=', $row['inn'])->first();
        //            if ($contractor == null) {
        //                $hasError = true;
        //            }
        //            $payout->user_id = $contractor->id;
        //
        //            $job_category = $job_categories->where('id', $row['job_category_id'])->first();
        //            if ($job_category == null) {
        //                $hasError = true;
        //            }
        //            $payout->job_category_id = $row['job_category_id'];
        //
        //            $payout->sum = floatval($row['sum']);
        //            $payout->status = 'draft';
        //
        //            if ($hasError == true) {
        //                DB::rollBack();
        //                return response()
        //                    ->json([
        //                        'title' => 'Ошибка',
        //                        'message' => 'Необходимо исправить ошибки и повторно загрузить файл.'
        //                    ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
        //            }
        //            try {
        //                $payout->save();
        //            } catch (\Throwable $e){
        //                DB::rollBack();
        //                return response()
        //                    ->json([
        //                        'title' => 'Ошибка',
        //                        'message' => 'Ошибка создания выплаты ' . $e->getMessage()
        //                    ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
        //            }
        //
        //
        //            try {
        //                CreateAct::dispatchNow($payout);
        //            } catch (\Throwable $e){}
        //
        //            /**
        //             * Процессинг выплаты
        //             */
        //            try {
        //                PayoutProcess::dispatchNow($payout);
        //            } catch (\Throwable $e){
        //                //
        //            }
        //        }

        DB::commit();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Выплаты созданы',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function annulate(Request $request)
    {
        $me = Auth::user();

        $payout = Payout::where('id', '=', $request->payout_id)->first();
        if ($payout->user_id != $me->id) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Чек может быть анулирован только пользователем, который выдал его',
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $Inn = $payout->user->inn;
        $ReceiptId = $payout->receipt_id;
        $ReasonCode = $request->annulate_reason;

        try {
            AnnulateReceipt::dispatchNow($Inn, $ReceiptId, $ReasonCode);
        } catch (\Throwable $e) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не удалось анулировать чек. Ошибка: ' . $e->getMessage() . ' Попробуйте повторить процедуру позже или обратитесь в техническую поддержку.',
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $payout->status = 'canceled';
        $payout->save();

        $project = Project::find($payout->task->project_id);

        $project->users->each(function ($user) use ($payout) {
            try {
                $notification = new Notification;
                $notification->user_id = $user->id;
                $notification->is_readed = false;
                $notification->from = 'Платформа';
                $notification->subject = 'Аннулирован чек по задаче «' . $payout->task->name . '»';
                $notification->text = "Исполнитель " . $payout->user->name .  "аннулировал <a href={$payout->receipt_url}>чек</a> по задаче «{$payout->task->name}»";
                $notification->plain_text = "Исполнитель " . $payout->user->name .  "аннулировал чек по задаче «{$payout->task->name}»";
                $notification->action = [
                    'type' => NotificationTypes::ENTITY,
                    'entity' => NotificationEntities::PAYMENT,
                    'entity_id' => $payout->id
                ];
                $notification->save();
            } catch (\Throwable $e) {
            }
        });
        try {
            $notification = new Notification;
            $notification->user_id = $payout->project->company->signer_user_id;
            $notification->is_readed = false;
            $notification->from = 'Платформа';
            $notification->subject = 'Аннулирован чек по задаче «' . $payout->task->name . '»';
            $notification->text = "Исполнитель " . $payout->user->name .  "аннулировал <a href={$payout->receipt_url}>чек</a> по задаче «{$payout->task->name}»";
            $notification->plain_text = "Исполнитель " . $payout->user->name .  "аннулировал чек по задаче «{$payout->task->name}»";
            $notification->action = [
                'type' => NotificationTypes::ENTITY,
                'entity' => NotificationEntities::PAYMENT,
                'entity_id' => $payout->id
            ];
            $notification->save();
        } catch (\Throwable $e) {
        }


        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Чек анулирован',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function info(Request $request)
    {
        $payout_id = $request->payout_id;

        $payout = Payout::with(['project', 'user', 'task', 'jobCategory'])->findOrFail($payout_id);
        $view = view('taskModal', ['payout' => $payout])->render();
        return response()->json([
            'view' => $view
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    //TODO нужен ли???
    public function infoPayout(Request $request)
    {
        $payout_id = $request->payout_id;

        $payout = Payout::with(['project', 'user', 'task', 'jobCategory'])->findOrFail($payout_id);

        $payoutData = [
            'id' => $payout->id,
            'status_code' => $payout->status,
            'status' => $payout->getTranslatedStatus(),
            'date' => "{$payout->getCreatedDateAttribute()} в {$payout->getCreatedTime()}",
            'comany' => $payout->task->company()->name,
            'name' => $payout->user->name,
            'card' => $payout->user->bankAccount->hiddenCard(),
            'task_id' => $payout->task->id,
            'task_name' => $payout->task->name,
            'sum' => $payout->task->sum,
            'receipt_url' => $payout->getReceiptUrl(!env('FNS_OFFLINE_MODE')),
        ];

        return response()->json([
            'title' => 'Успешно',
            'message' => 'Информация о платеже получена',
            'payout' => $payoutData
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
