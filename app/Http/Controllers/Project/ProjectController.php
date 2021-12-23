<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\DocumentApiProjectsListRequest;
use App\Models\Company;
use Illuminate\Http\Request;

use DB;
use Auth;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectUser;
use Arr;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends Controller
{

    public function projectList(DocumentApiProjectsListRequest $request)
    {
        $requestData = $request->validated();

        $organizationInn = $requestData['inn_organization'];

        $organization = Company::findByInn($organizationInn);

        $projects = Project::where('company_id', $organization->id)->get(['id', 'name', 'created_at']);
        // $projects = $request->user()->getProjects();

        return response()->json($projects);
    }

    public function list(Request $request)
    {
        $me = Auth::user();

        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        $projects = [];
        if ($me->isCompanyAdmin()) {
            $projects = Project::where('company_id', '=', $me->company_id);
        } else {
            $projects = $me->projects();
        }

        if ($request->get('name')) {
            $projects->when($request->name, function ($query, $name) {
                return $query->where('name', 'like', "%$name%");
            });
        }

        if ($request->get('employee_id')) {
            $projects->when($request->employee_id, function ($query, $userId) {
                return $query->whereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            });
        }

        $projects->orderBy('created_at', 'desc');

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Список проектов загружен',
                'projects' => $projects->get()
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function getCompanyProjects(Request $request)
    {
        $me = Auth::user();
        if ($me->is_client == false) {
            return abort(403);
        }

        return [
            'projects' => $request->user()->getProjects()->get()
        ];
    }


    /**
     * Список проектов клиента
     */
    public function clientProjectsDatatable(Request $request)
    {
        $me = Auth::user();
        if ($me->is_client == false) {
            return abort(403);
        }

        $projects = $request->user()->getProjects()
            ->withCount(['tasks']);

        if ($request->filter) {
            if (Arr::exists($request->filter, 'name')) {
                $name = $request->filter['name'];
                $projects = $projects->where('name', 'LIKE', "%$name%");
            }
        }

        $dataTable = DataTables::eloquent($projects);

        $dataTable = $dataTable->addColumn('tasks_count', function (Project $project) {
            return $project->tasks_count;
        });


        //$dataTable = $dataTable->addColumn('orders_count', function(Project $project) {
        //	return $project->orders_count;
        //});
        //$dataTable = $dataTable->orderColumn('orders_count', function ($query, $order) {
        //	$query->orderBy('orders_count', $order);
        //});


        //$dataTable = $dataTable->addColumn('users_count', function(Project $project) {
        //	return $project->users_count;
        //});
        //$dataTable = $dataTable->orderColumn('users_count', function ($query, $order) {
        //	$query->orderBy('users_count', $order);
        //});


        $dataTable = $dataTable->addColumn('created_date', function (Project $project) {
            return Carbon::parse($project->created_at)->format('d.m.Y');
        });

        $dataTable = $dataTable->orderColumn('created_date', function ($query, $order) {
            $query->orderBy('created_at', $order);
        });

        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }




    //    public function contractorProjectsDatatable(Request $request)
    //    {
    //        $me = Auth::user();
    //        if ($me->is_selfemployed == false) {
    //            return abort(403);
    //        }
    //
    //        $projects = Project::query()
    //            ->rightJoin('projects_users', 'projects_users.project_id', '=', 'projects.id')
    //            ->where('projects_users.user_id', '=', $me->id)
    //            ->leftJoin('job_categories', 'job_categories.id', '=', 'projects_users.job_category_id');
    //
    //        $select = [];
    //        $select[] = DB::raw('projects.id as project_id');
    //        $select[] = DB::raw('projects.name');
    //        $select[] = DB::raw('projects.created_at');
    //        $select[] = DB::raw('projects_users.id as project_user_id');
    //        $select[] = DB::raw('projects_users.job_category_id');
    //        $select[] = DB::raw('projects_users.document_id');
    //        $select[] = DB::raw('projects_users.accepted_by_user');
    //        $select[] = DB::raw('job_categories.id');
    //        $select[] = DB::raw('job_categories.name job_category_name');
    //        $projects = $projects->select($select);
    //
    //        $dataTable = DataTables::eloquent($projects);
    //
    //
    //        $dataTable = $dataTable->addColumn('project_id', function (Project $project) {
    //            return $project->project_id;
    //        });
    //        $dataTable = $dataTable->orderColumn('project_id', function ($query, $order) {
    //            $query->orderBy('projects.id', $order);
    //        });
    //
    //        $dataTable = $dataTable->addColumn('name', function (Project $project) {
    //            return $project->name;
    //        });
    //        $dataTable = $dataTable->orderColumn('name', function ($query, $order) {
    //            $query->orderBy('projects.name', $order);
    //        });
    //
    //        $dataTable = $dataTable->addColumn('job_category_name', function (Project $project) {
    //            return $project->job_category_name;
    //        });
    //        $dataTable = $dataTable->orderColumn('job_category_name', function ($query, $order) {
    //            $query->orderBy('job_categories.name', $order);
    //        });
    //
    //        $dataTable = $dataTable->addColumn('accepted_by_user', function (Project $project) {
    //            return $project->accepted_by_user;
    //        });
    //        $dataTable = $dataTable->orderColumn('accepted_by_user', function ($query, $order) {
    //            $query->orderBy('projects_users.accepted_by_user', $order);
    //        });
    //
    //        $dataTable = $dataTable->addColumn('created_date', function (Project $project) {
    //            return Carbon::parse($project->created_at)->format('d.m.Y');
    //        });
    //
    //        $dataTable = $dataTable->orderColumn('created_date', function ($query, $order) {
    //            $query->orderBy('created_at', $order);
    //        });
    //
    //        $dataTable = $dataTable->smart(true);
    //        return $dataTable->make(true);
    //    }




    public function get(Request $request)
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
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные проекта загружены',
                'project' => $project
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function create(Request $request)
    {
        $me = Auth::user();
        if ($me->company_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не установлена компания клиента'
                ], 403, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $project = new Project;
        $project->fill([
            'company_id' => $me->company_id,
            'name' => $request->name
        ]);
        $project->save();

        $canAccess = $me->hasAccessToProject($project->id);
        if (!$canAccess) {
            $project->users()->sync([$me->id]);
        }
        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Проект успешно создан!',
                'project_id' => $project->id
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }





    public function addContractors(Request $request)
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

        if ($request->contractors == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не передан список исполнителей'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        if ($request->job_category_id == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не указана категория для добавления исполнителей в проект'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }


        $users = User::whereIn('id', $request->contractors)->get();
        DB::beginTransaction();
        foreach ($users as $user) {
            if ($user->is_selfemployed == true) {
                $ProjectUser = ProjectUser::updateOrCreate(
                    ['user_id' => $user->id, 'project_id' => $project->id],
                    ['job_category_id' => $request->job_category_id]
                );
            }
        }
        DB::commit();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Исполнители добавлены в проект'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }





    public function acceptProject(Request $request)
    {
        $me = Auth::user();
        if ($me->is_selfemployed == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Доступно только для исполнителей'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $projectUser = ProjectUser::where('user_id', '=', $me->id)
            ->where('project_id', '=', $request->project_id)
            ->first();

        if ($projectUser == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Предложение не найдено'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $projectUser->accepted_by_user = true;
        $projectUser->save();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы приняли проект'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function declineProject(Request $request)
    {
        $me = Auth::user();

        if ($me->is_selfemployed == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Доступно только для исполнителей'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $projectUser = ProjectUser::where('user_id', '=', $me->id)
            ->where('project_id', '=', $request->project_id)
            ->first();

        if ($projectUser == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Предложение не найдено'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $projectUser->delete();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы отказались от проекта'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
