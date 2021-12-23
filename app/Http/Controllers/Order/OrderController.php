<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\ExcelHelper;
use App\Services\NotificationsService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use DB;
use Auth;
use Storage;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;
use App\Models\Order;
use App\Models\Task;
use App\Models\JobCategory;
use App\Models\ProjectUser;


use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TasksImport;
use Illuminate\Support\Facades\Log;


use App\Jobs\Documents\CreateWorkOrder;


class OrderController extends Controller
{
    protected NotificationsService $notificationsService;

    /**
     * @param NotificationsService $notificationsService
     */
    public function __construct(NotificationsService $notificationsService)
    {
        $this->notificationsService = $notificationsService;
    }

//    public function list(Request $request)
//    {
//        $me = Auth::user();
//        if ($me->company_id == null) {
//            return response()
//                ->json([
//                    'title' => 'Ошибка',
//                    'message' => 'Не установлена компания клиента'
//                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
//        }
//
//        $project = Project::where('id', '=', $request->project_id)->first();
//        if ($project == null) {
//            return response()
//                ->json([
//                    'title' => 'Ошибка',
//                    'message' => 'Проект не существует'
//                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
//        }
//
//        $orders = Order::where('project_id', '=', $project->id)->orderBy('created_at', 'desc')->get();
//        return response()
//            ->json([
//                'title' => 'Успешно',
//                'message' => 'Список ведомостей загружен',
//                'orders' => $orders
//            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
//    }


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


        $project = Project::where('id', '=', $request->project_id)->first();
        if ($project == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Проект не существует'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        if ($request->hasFile('file') == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не передан файл с реестром!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $validMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $mimeType = mime_content_type($request->file->getPathname());
        /*        if (in_array($mimeType, $validMimeTypes) == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Плохой тип файла (' . $mimeType . ')'
                ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
        }*/

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
        $has_errors = false;
        foreach ($page0 as $i => $row) {
            $row = array_values($row);
            if (Arr::get($row, 0) == null or Arr::get($row, 1) == null) {
                continue;
            }
            $table[] = [
                'job_name'            => trim(Arr::get($row, 0)),
                'inn'                 => trim(Arr::get($row, 1)),
                'contractor'          => trim(Arr::get($row, 2)),
                'job_start_date'      => trim(Arr::get($row, 3)),
                'job_finish_date'     => trim(Arr::get($row, 4)),
                'sum'                 => trim(Arr::get($row, 5))
            ];
        }

        if (count($table) == 0) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не удалось извлечь из файла данные'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        //        $job_categories = JobCategory::where('parent_id', '!=', null)
        //            ->get();

        //        $project_users = ProjectUser::where('project_id', '=', $project->id)
        //            ->leftJoin('users', 'users.id', '=', 'projects_users.user_id')->get();

        foreach ($table as $i => $tr) {
            $errors = [];

            //            $job_category_name = trim($tr['job_category_name']);
            //            $job_category = $job_categories->where('name', $job_category_name)->first();
            //            if ($job_category == null) {
            //                $table[$i]['job_category_id'] = null;
            //                $errors[]= 'Категория «' . $job_category_name . '» не существует в справочнике ФНС';
            //                $has_errors = true;
            //            } else {
            //                $table[$i]['job_category_id'] = $job_category->id;
            //            }

            $table[$i]['job_name'] = $tr['job_name'];

            $inn = preg_replace("/\D/", "", trim($tr['inn']));
            $table[$i]['inn'] = $inn;
            $contractor = User::whereInn($inn)->first();
            if ($contractor == null) {
                $errors[] = 'Исполнитель с ИНН «' . $inn . '» не зарегистрирован в ЯЗанят';
                $has_errors = true;
            }

            $table[$i]['job_start_date'] = ExcelHelper::todmY($tr['job_start_date']);
            $table[$i]['job_finish_date'] = ExcelHelper::todmY($tr['job_finish_date']);

            $table[$i]['sum'] = floatval($tr['sum']);

            $table[$i]['errors'] = $errors;
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные получены',
                'table' => $table,
                'has_errors' => $has_errors,
                'errors' => $errors,
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    //    public function payForTasksExcel(Request $request)
    //    {
    //        $me = Auth::user();
    //        if ($me->company_id == null) {
    //            return response()
    //                ->json([
    //                    'title' => 'Ошибка',
    //                    'message' => 'Не установлена компания клиента'
    //                ], 403, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    //        }
    //
    //
    //        $project = Project::where('id', '=', $request->project_id)->first();
    //        if ($project == null) {
    //            return response()
    //                ->json([
    //                    'title' => 'Ошибка',
    //                    'message' => 'Проект не существует'
    //                ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    //        }
    //
    //        if ($request->hasFile('file') == false) {
    //            return response()
    //                ->json([
    //                    'title' => 'Ошибка',
    //                    'message' => 'Не передан файл с реестром!'
    //                ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    //        }
    //
    //        $path = Storage::disk('local')->putFile('tasks_excels', $request->file('file'));
    //
    //        try {
    //            $data = Excel::toArray(new TasksImport, $path);
    //        } catch(\Throwable $e) {
    //            return response()
    //                ->json([
    //                    'title' => 'Ошибка',
    //                    'message' => 'Не удалось извлечь из файла данные',
    //                    'exception' => $e->getMessage()
    //                ], 400, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    //        }
    //
    //        $page0 = $data[0];
    //        $table = [];
    //
    //        foreach ($page0 as $i => $row) {
    //            $row = array_values($row);
    //            if (Arr::get($row, 0) == null OR Arr::get($row, 1) == null) {
    //                continue;
    //            }
    //            $table[]=[
    //                'job_name'            => trim(Arr::get($row, 0)),
    //                'inn'                 => trim(Arr::get($row, 1)),
    //                'contractor'          => trim(Arr::get($row, 2)),
    //                'job_start_date'      => trim(Arr::get($row, 3)),
    //                'job_finish_date'     => trim(Arr::get($row, 4)),
    //                'sum'                 => trim(Arr::get($row, 5))
    //            ];
    //        }
    //
    //        return $table;
    //    }

    /**
     *
     */
    public function new(Request $request, int $projectId)
    {
        Log::channel('debug')->debug('test');

        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $project = Project::where('id', '=', $request->project_id)->first();
        if ($project == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Проект не существует'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }


        $import_data = $request->import_data;

        // TelegramAdminBotClient::sendAdminNotification(json_encode($import_data));

        $job_categories = JobCategory::where('parent_id', '!=', null)
            ->get();

        //        $project_users = ProjectUser::where('project_id', '=', $project->id)
        //            ->leftJoin('users', 'users.id', '=', 'projects_users.user_id')->get();


        DB::beginTransaction();
        //        $order = new Order;
        //        $order->project_id = $project->id;
        //        $order->name = 'Ведомость';
        //        $order->save();

        foreach ($import_data as $row) {
            $hasError = false;

            $task = new Task;
            $task->name = $row['job_name'];
            //            $task->order_id = $order->id;
            $task->project_id = $project->id;

            $contractor = User::whereInn($row['inn'])->first();
            if ($contractor == null) {
                $hasError = true;
                continue;
            }

            $job_category = $job_categories->find(3);
            if ($job_category == null) {
                $hasError = true;
            }
            $task->job_category_id = $job_category->id;

            $task->sum = floatval($row['sum']);
            $task->date_from = $row['job_start_date'];
            $task->date_till = $row['job_finish_date'];
            Log::channel('debug')->debug('Order controller', $contractor->toArray());

            if ($hasError == true) {
                DB::rollBack();
                return response()
                    ->json([
                        'title' => 'Ошибка',
                        'message' => 'Необходимо исправить ошибки и повторно загрузить файл.'
                    ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }
            try {
                $task->save();
                $this->notificationsService->createInvitationNotification($task, $contractor);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()
                    ->json([
                        'title' => 'Ошибка',
                        'message' => 'Ошибка чтения полей в файле. Необходимо исправить ошибки и повторно загрузить файл. ' . $e->getMessage()
                    ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
            }
        }

        DB::commit();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные импортированы',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
