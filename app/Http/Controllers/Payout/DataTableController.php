<?php

namespace App\Http\Controllers\Payout;

use App\Http\Controllers\Controller;
use App\Services\PayoutsService;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;
use DB;

use App\Models\User;
use App\Models\Project;
use App\Models\Order;
use App\Models\Task;
use App\Models\Payout;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class DataTableController extends Controller
{

    protected PayoutsService $payoutsService;

    /**
     * @param PayoutsService $payoutsService
     */
    public function __construct(PayoutsService $payoutsService)
    {
        $this->payoutsService = $payoutsService;
    }


    public function datatable(Request $request)
    {
        $me = Auth::user();

        $payouts = Payout::query();
        $payouts = $payouts->orderByDesc('payouts.created_at');

        if ($request->project_id) {
            $payouts = $payouts->where('payouts.project_id', '=', $request->project_id);
        }

        if ($me->is_selfemployed == true) {
            $payouts = $payouts->where('payouts.user_id', '=', $me->id);
        }
        $payouts = $payouts->leftJoin('users', 'payouts.user_id', '=', 'users.id');
        $payouts = $payouts->leftJoin('job_categories', 'payouts.job_category_id', '=', 'job_categories.id');
        $payouts = $payouts->leftJoin('tasks', 'tasks.id', '=', 'payouts.task_id');

        $payouts = $payouts->leftJoin('projects', 'projects.id', '=', 'tasks.project_id');
        $payouts = $payouts->leftJoin('companies', 'companies.id', '=', 'projects.company_id');




        $select = [];
        $select[] = DB::raw('payouts.id as id');
        $select[] = DB::raw('payouts.sum as sum');
        $select[] = DB::raw('payouts.status as status');
        //$select[] = DB::raw('payouts.user_id as user_id');
        $select[] = DB::raw('payouts.created_at as created_at');
        $select[] = DB::raw('payouts.description as description');
        $select[] = DB::raw('payouts.receipt_url as receipt_url');

        $select[] = DB::raw('tasks.id as task_id');
        $select[] = DB::raw('tasks.name as task_name');

        $select[] = DB::raw('users.inn as user_inn');
        $select[] = DB::raw('users.firstname as user_firstname');
        $select[] = DB::raw('users.lastname as user_lastname');
        $select[] = DB::raw('users.patronymic as user_patronymic');
        $select[] = DB::raw('job_categories.id as job_category_id');
        $select[] = DB::raw('job_categories.name as job_category_name');
        $select[] = DB::raw('users.taxpayer_registred_as_npd as taxpayer_registred_as_npd');

        $select[] = DB::raw('companies.id as company_id');
        $select[] = DB::raw('companies.name as company_name');
        $select[] = DB::raw('companies.inn as company_inn');

        $payouts = $payouts->select($select);

        $dataTable = DataTables::eloquent($payouts);

        $dataTable = $dataTable->addColumn('id', function (Payout $payout) {
            return $payout->id;
        });
        $dataTable = $dataTable->filterColumn('id', function ($query, $keyword) {
            $query->whereRaw('payouts.id = ?', ["{$keyword}"]);
        });
        $dataTable = $dataTable->orderColumn('id', function ($query, $order) {
            $query->orderBy('payouts.id', $order);
        });


        $dataTable = $dataTable->addColumn('task_name', function (Payout $payout) {
            return $payout->task_name;
        });
        $dataTable = $dataTable->filterColumn('task_name', function ($query, $keyword) {
            $query->whereRaw('tasks.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('task_name', function ($query, $order) {
            $query->orderBy('tasks.name', $order);
        });


        $dataTable = $dataTable->addColumn('description', function (Payout $payout) {
            return $payout->description;
        });

        $dataTable = $dataTable->addColumn('receipt_url', function (Payout $payout) {
            return $payout->receipt_url;
        });


        $dataTable = $dataTable->addColumn('user_name', function (Payout $payout) {
            return $payout->user->name;
        });
        $dataTable = $dataTable->filterColumn('name', function ($query, $keyword) {
            $query->whereRaw('users.lastname like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('name', function ($query, $order) {
            $query->orderBy('users.lastname', $order);
        });


        $dataTable = $dataTable->addColumn('user_inn', function (Payout $payout) {
            return $payout->user_inn;
        });
        $dataTable = $dataTable->filterColumn('user_inn', function ($query, $keyword) {
            $query->whereRaw('users.inn like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('user_inn', function ($query, $order) {
            $query->orderBy('users.inn', $order);
        });


        $dataTable = $dataTable->addColumn('job_category_name', function (Payout $payout) {
            return $payout->job_category_name;
        });
        $dataTable = $dataTable->filterColumn('job_category_name', function ($query, $keyword) {
            $query->whereRaw('job_categories.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('job_category_name', function ($query, $order) {
            $query->orderBy('job_categories.name', $order);
        });


        $dataTable = $dataTable->addColumn('sum', function (Payout $payout) {
            return number_format($payout->sum, 2, ',', ' ') . ' ₽';
        });
        $dataTable = $dataTable->filterColumn('sum', function ($query, $keyword) {
            $query->whereRaw('sum like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('sum', function ($query, $order) {
            $query->orderBy('sum', $order);
        });


        $dataTable = $dataTable->addColumn('created_datetime', function (Payout $payout) {
            return Carbon::parse($payout->created_at)->format('d.m.Y в H:i:s');
        });
        $dataTable = $dataTable->orderColumn('created_datetime', function ($query, $order) {
            $query->orderBy('payouts.created_at', $order);
        });


        $only = [
            'id',
            'task_id',
            'task_name',
            'description',
            'receipt_url',
            'user_name',
            'user_inn',
            'job_category_name',
            'sum',
            'status',
            'created_datetime',
            'company_name',
            'company_id',
            'company_inn',
        ];

        $dataTable = $dataTable->only($only);

        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }

    public function datatableAll(Request $request)
    {
        $company = JWTAuth::parseToken()->toUser()?->company ?? throw new \Exception('Не установлена компания пользователя');


        return $this->payoutsService->getCompanyPayoutsDatatable($company, $request->get('filter') ?? [])->make(true);
    }
}
