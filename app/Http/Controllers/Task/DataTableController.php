<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use DB;
use Auth;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;

use Yajra\DataTables\Facades\DataTables;


class DataTableController extends Controller
{

    public function datatable(Request $request)
    {
        $me = Auth::user();

        $select = [];
        $only = [];

        $tasks = Task::query();
        $tasks= $tasks->orderByDesc('tasks.created_at');
        if ($request->project_id != null) {
            $tasks = $tasks->where('project_id', '=', $request->project_id);
        }

        // Добавляем в выборку основные нужые поля
        $select[] = DB::raw('tasks.id as id');
        $select[] = DB::raw('tasks.name as name');
       // $select[] = DB::raw('tasks.user_id as user_id');
        $select[] = DB::raw('tasks.status as status');
        $select[] = DB::raw('tasks.date_from as date_from');
        $select[] = DB::raw('tasks.date_till as date_till');
        $select[] = DB::raw('tasks.sum as sum');
        $select[] = DB::raw('tasks.created_at as created_at');
        $select[] = DB::raw('tasks.is_sum_confirmed as is_sum_confirmed');

        $only[] = 'id';
        $only[] = 'name';
        $only[] = 'status';
        $only[] = 'date_from';
        $only[] = 'date_till';
        $only[] = 'sum';
        $only[] = 'is_sum_confirmed';


        // Джойним категории работ
        $tasks = $tasks->leftJoin('job_categories', 'tasks.job_category_id', '=', 'job_categories.id');
        $select[] = DB::raw('job_categories.id as job_category_id');
        $select[] = DB::raw('job_categories.name as job_category_name');
        $only[] = 'job_category_id';
        $only[] = 'job_category_name';

        $tasks = $tasks->leftJoin('projects', 'tasks.project_id', '=', 'projects.id');
        $tasks = $tasks->leftJoin('companies', 'projects.company_id', '=', 'companies.id');
        $select[] = DB::raw('projects.id as project_id');
        $select[] = DB::raw('projects.company_id as project_company_id');
        $select[] = DB::raw('companies.id as company_id');
        $select[] = DB::raw('companies.name as company_name');
        $only[] = 'company_name';
        $only[] = 'company_id';


        // количество офферов от исполнителей

        $offersSubquery = DB::table('offers')->groupBy('task_id')->select([
            'task_id',
            DB::raw('count(user_id) as count')
        ]);
        $tasks = $tasks->leftJoinSub($offersSubquery, 'offers_count_table', function ($join) {
            $join->on('offers_count_table.task_id', '=', 'tasks.id');
        });
        $only[] = 'offers_count';
        $select[] = DB::raw('offers_count_table.count as offers_count');

        // Если это запрос от клиента
        if ($me->is_client) {

            // выбранный исполнитель
            $tasks = $tasks->with('user');
            $tasks = $tasks->leftJoin('users', 'tasks.user_id', '=', 'users.id');
            $select[] = DB::raw('users.inn as user_inn');
            $select[] = DB::raw('users.firstname as user_firstname');
            $select[] = DB::raw('users.lastname as user_lastname');
            $select[] = DB::raw('users.patronymic as user_patronymic');

            $only[] = 'user_id';
            $only[] = 'user_inn';
            $only[] = 'user_name';
        }

        if ($me->is_selfemployed) {
            if ($request->route('task_group') == 'new') {
                $tasks = $tasks->where('status', '=', 'new');

                $myOfferSubquery = DB::table('offers')->where('user_id', '=', $me->id)->select([
                    DB::raw('offers.id as my_offer_id'),
                    DB::raw('offers.task_id')
                ]);
                $tasks = $tasks->leftJoinSub($myOfferSubquery, 'my_offers_table', function ($join) {
                    $join->on('tasks.id', '=', 'my_offers_table.task_id');
                });
                $select[] = DB::raw('my_offers_table.my_offer_id as my_offer_id');

                $only[] = 'my_offer_id';
                $only[] = 'is_sum_confirmed';
            } else if ($request->tasks_group == 'my') {
                $tasks = $tasks->where('user_id', '=', $me->id);
            }


            //$tasks = $tasks->leftJoin('offers', 'tasks.user_id', '=', 'users.id');


        }


        if ($request->filter) {

            if (Arr::exists($request->filter, 'job_category_id')) {
                $tasks = $tasks
                    ->where('tasks.job_category_id', '=', $request->filter['job_category_id']);
            }
        }

        $tasks = $tasks->select($select);

        $dataTable = DataTables::eloquent($tasks);

        $dataTable = $dataTable->addColumn('offers_count', function (Task $task) {
            //ddh($task);
            return intval($task->offers_count);
        });


        $dataTable = $dataTable->addColumn('company_id', function (Task $task) {
            return $task->company_id;
        });

        $dataTable = $dataTable->addColumn('company_name', function (Task $task) {
            return $task->company_name;
        });
        $dataTable = $dataTable->filterColumn('company_name', function ($query, $keyword) {
            $query->whereRaw('companies.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('company_name', function ($query, $order) {
            $query->orderBy('companies.name', $order);
        });

        $dataTable = $dataTable->addColumn('job_category_name', function (Task $task) {
            return $task->job_category_name;
        });
        $dataTable = $dataTable->filterColumn('job_category_name', function ($query, $keyword) {
            $query->whereRaw('job_categories.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('job_category_name', function ($query, $order) {
            $query->orderBy('job_categories.name', $order);
        });



        $dataTable = $dataTable->addColumn('sum', function (Task $task) {
            return number_format($task->sum, 2, ',', ' ') . ' ₽';
        });
        $dataTable = $dataTable->filterColumn('sum', function ($query, $keyword) {
            $query->whereRaw('sum like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('sum', function ($query, $order) {
            $query->orderBy('sum', $order);
        });

        if ($me->is_client) {
            $dataTable = $dataTable->addColumn('user_name', function (Task $task) {
                if ($task->user != null) {
                    return $task->user->name;
                }
                return null;
            });
            $dataTable = $dataTable->filterColumn('name', function ($query, $keyword) {
                $query->whereRaw('users.lastname like ?', ["{$keyword}%"]);
            });
            $dataTable = $dataTable->orderColumn('name', function ($query, $order) {
                $query->orderBy('users.lastname', $order)
                    ->orderBy('offers_count_table.count', $order);
            });

            $dataTable = $dataTable->addColumn('user_id', function (Task $task) {
                return $task->user_id;
            });

            $dataTable = $dataTable->addColumn('user_inn', function (Task $task) {
                return $task->user_inn;
            });
            $dataTable = $dataTable->filterColumn('user_inn', function ($query, $keyword) {
                $query->whereRaw('users.inn like ?', ["{$keyword}%"]);
            });
            $dataTable = $dataTable->orderColumn('user_inn', function ($query, $order) {
                $query->orderBy('users.inn', $order);
            });
        }

        if ($me->is_selfemployed) {
            if ($request->tasks_group) {
                if ($request->tasks_group == 'new') {
                    $dataTable = $dataTable->addColumn('my_offer_id', function (Task $task) {
                        return $task->my_offer_id;
                    });
                    $dataTable = $dataTable->orderColumn('my_offer_id', function ($query, $order) {
                        $query->orderBy('my_offers_table.my_offer_id', $order);
                    });
                }
            }
        }


        $dataTable = $dataTable->addColumn('date_from', function (Task $task) {
            return Carbon::parse($task->date_from)->format('d.m.Y');
        });
        $dataTable = $dataTable->addColumn('date_till', function (Task $task) {
            return Carbon::parse($task->date_till)->format('d.m.Y');
        });

        $dataTable = $dataTable->orderColumn('date', function ($query, $order) {
            $query->orderBy('date_from', $order);
        });


        $only[] = 'created_datetime';

        $dataTable = $dataTable->addColumn('created_datetime', function (Task $task) {
            return Carbon::parse($task->created_at)->format('d.m.Y в H:i:s');
        });
        $dataTable = $dataTable->filterColumn('created_datetime', function ($query, $keyword) {
            $query->whereRaw('tasks.created_at like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('created_datetime', function ($query, $order) {
            $query->orderBy('tasks.created_at', $order);
        });


        $dataTable = $dataTable->only($only);
        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }

    public function contractorDataTable(Request $request)
    {
        $me = Auth::user();

        $select = [];
        $only = [];

        $tasks = Task::query();

        // Добавляем в выборку основные нужые поля
        $select[] = DB::raw('tasks.id as id');
        $select[] = DB::raw('tasks.name as name');
        $select[] = DB::raw('tasks.user_id as user_id');
        $select[] = DB::raw('tasks.status as status');
        $select[] = DB::raw('tasks.date_from as date_from');
        $select[] = DB::raw('tasks.date_till as date_till');
        $select[] = DB::raw('tasks.sum as sum');
        $select[] = DB::raw('tasks.created_at as created_at');
        $select[] = DB::raw('tasks.is_sum_confirmed as is_sum_confirmed');

        $only[] = 'id';
        $only[] = 'name';
        $only[] = 'status';
        $only[] = 'date_from';
        $only[] = 'date_till';
        $only[] = 'sum';
        $only[] = 'is_sum_confirmed';


        // Джойним категории работ
        $tasks = $tasks->leftJoin('job_categories', 'tasks.job_category_id', '=', 'job_categories.id');
        $select[] = DB::raw('job_categories.id as job_category_id');
        $select[] = DB::raw('job_categories.name as job_category_name');
        $only[] = 'job_category_id';
        $only[] = 'job_category_name';

        $tasks = $tasks->leftJoin('projects', 'tasks.project_id', '=', 'projects.id');
        $tasks = $tasks->leftJoin('companies', 'projects.company_id', '=', 'companies.id');
        $select[] = DB::raw('projects.id as project_id');
        $select[] = DB::raw('projects.company_id as project_company_id');
        $select[] = DB::raw('companies.id as company_id');
        $select[] = DB::raw('companies.name as company_name');
        $only[] = 'company_name';
        $only[] = 'company_id';


        // количество офферов от исполнителей

        $offersSubquery = DB::table('offers')->groupBy('task_id')->select([
            'task_id',
            DB::raw('count(user_id) as count')
        ]);
        $tasks = $tasks->leftJoinSub($offersSubquery, 'offers_count_table', function ($join) {
            $join->on('offers_count_table.task_id', '=', 'tasks.id');
        });
        $only[] = 'offers_count';
        $select[] = DB::raw('offers_count_table.count as offers_count');

        if ($request->route('task_group') == 'new') {
            $tasks = $tasks->where('status', '=', 'new');

            $myOfferSubquery = DB::table('offers')->where('user_id', '=', $me->id)->select([
                DB::raw('offers.id as my_offer_id'),
                DB::raw('offers.task_id')
            ]);
            $tasks = $tasks->leftJoinSub($myOfferSubquery, 'my_offers_table', function ($join) {
                $join->on('tasks.id', '=', 'my_offers_table.task_id');
            });

            $select[] = DB::raw('my_offers_table.my_offer_id as my_offer_id');

            $only[] = 'my_offer_id';
            $only[] = 'is_sum_confirmed';
        }
        else if ($request->route('task_group') == 'my') {
            $tasks = $tasks->where('user_id', '=', $me->id);
        }


        if ($request->get('filter')) {

            if (Arr::exists($request->filter, 'job_category_id')) {
                $tasks = $tasks
                    ->where('tasks.job_category_id', '=', $request->filter['job_category_id']);
            }
        }

        $tasks = $tasks->select($select);

        $dataTable = DataTables::eloquent($tasks);

        $dataTable = $dataTable->addColumn('offers_count', function (Task $task) {
            //ddh($task);
            return intval($task->offers_count);
        });


        $dataTable = $dataTable->addColumn('company_id', function (Task $task) {
            return $task->company_id;
        });

        $dataTable = $dataTable->addColumn('company_name', function (Task $task) {
            return $task->company_name;
        });
        $dataTable = $dataTable->filterColumn('company_name', function ($query, $keyword) {
            $query->whereRaw('companies.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('company_name', function ($query, $order) {
            $query->orderBy('companies.name', $order);
        });

        $dataTable = $dataTable->addColumn('job_category_name', function (Task $task) {
            return $task->job_category_name;
        });
        $dataTable = $dataTable->filterColumn('job_category_name', function ($query, $keyword) {
            $query->whereRaw('job_categories.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('job_category_name', function ($query, $order) {
            $query->orderBy('job_categories.name', $order);
        });



        $dataTable = $dataTable->addColumn('sum', function (Task $task) {
            return number_format($task->sum, 2, ',', ' ') . ' ₽';
        });
        $dataTable = $dataTable->filterColumn('sum', function ($query, $keyword) {
            $query->whereRaw('sum like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('sum', function ($query, $order) {
            $query->orderBy('sum', $order);
        });

        if ($me->is_client) {
            $dataTable = $dataTable->addColumn('user_name', function (Task $task) {
                if ($task->user != null) {
                    return $task->user->name;
                }
                return null;
            });
            $dataTable = $dataTable->filterColumn('name', function ($query, $keyword) {
                $query->whereRaw('users.lastname like ?', ["{$keyword}%"]);
            });
            $dataTable = $dataTable->orderColumn('name', function ($query, $order) {
                $query->orderBy('users.lastname', $order)
                    ->orderBy('offers_count_table.count', $order);
            });

            $dataTable = $dataTable->addColumn('user_id', function (Task $task) {
                return $task->user_id;
            });

            $dataTable = $dataTable->addColumn('user_inn', function (Task $task) {
                return $task->user_inn;
            });
            $dataTable = $dataTable->filterColumn('user_inn', function ($query, $keyword) {
                $query->whereRaw('users.inn like ?', ["{$keyword}%"]);
            });
            $dataTable = $dataTable->orderColumn('user_inn', function ($query, $order) {
                $query->orderBy('users.inn', $order);
            });
        }

        if ($me->is_selfemployed) {
            if ($request->tasks_group) {
                if ($request->tasks_group == 'new') {
                    $dataTable = $dataTable->addColumn('my_offer_id', function (Task $task) {
                        return $task->my_offer_id;
                    });
                    $dataTable = $dataTable->orderColumn('my_offer_id', function ($query, $order) {
                        $query->orderBy('my_offers_table.my_offer_id', $order);
                    });
                }
            }
        }


        $dataTable = $dataTable->addColumn('date_from', function (Task $task) {
            return Carbon::parse($task->date_from)->format('d.m.Y');
        });
        $dataTable = $dataTable->addColumn('date_till', function (Task $task) {
            return Carbon::parse($task->date_till)->format('d.m.Y');
        });

        $dataTable = $dataTable->orderColumn('date', function ($query, $order) {
            $query->orderBy('date_from', $order);
        });


        $only[] = 'created_datetime';

        $dataTable = $dataTable->addColumn('created_datetime', function (Task $task) {
            return Carbon::parse($task->created_at)->format('d.m.Y в H:i:s');
        });
        $dataTable = $dataTable->filterColumn('created_datetime', function ($query, $keyword) {
            $query->whereRaw('tasks.created_at like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('created_datetime', function ($query, $order) {
            $query->orderBy('tasks.created_at', $order);
        });


        $dataTable = $dataTable->only($only);
        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }
}
