<?php

namespace App\Services;

use App\Jobs\StartTaskPaymentProcessJob;
use App\Models\Company;
use App\Models\Document;
use App\Models\Payout;
use App\Models\Project;
use App\Services\Telegram\TelegramAdminBotClient;
use Barracuda\ArchiveStream\Archive as ArchiveStream;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PayoutsService
{
    public function getAllowedToRepayStatuses()
    {
        return ['error', 'canceled'];
    }

    public function getPayoutsQuery(Company $company, array $filter=[])
    {
        if (isset($filter['projects'])) {
            $companyProjectsIds = $filter['projects'];

            $companyProjectsIds = array_map(fn($item) => intval($item), $companyProjectsIds);
        }

        $companyProjectsIds = $companyProjectsIds ?? Project::whereCompanyId($company->id)->get()->pluck('id');

        $payouts = Payout::query();

        $payouts = $payouts->leftJoin('users', 'payouts.user_id', '=', 'users.id');
        $payouts = $payouts->leftJoin('job_categories', 'payouts.job_category_id', '=', 'job_categories.id');
        $payouts = $payouts->leftJoin('tasks', 'tasks.id', '=', 'payouts.task_id');

        $payouts = $payouts->leftJoin('projects', 'projects.id', '=', 'tasks.project_id');
        $payouts = $payouts->leftJoin('companies', 'companies.id', '=', 'projects.company_id');




        $select = [];
        $select[] = DB::raw('payouts.id as id');
        $select[] = DB::raw('payouts.sum as sum');
        $select[] = DB::raw('payouts.status as status');
        $select[] = DB::raw('payouts.user_id as user_id');
        $select[] = DB::raw('payouts.created_at as created_at');
        $select[] = DB::raw('payouts.description as description');
        $select[] = DB::raw('payouts.receipt_url as receipt_url');

        $select[] = DB::raw('tasks.id as task_id');
        $select[] = DB::raw('tasks.name as task_name');


        $select[] = DB::raw('projects.name as project_name');
        $select[] = DB::raw('projects.id as project_id');

        $select[] = DB::raw('users.inn as user_inn');
        $select[] = DB::raw('users.firstname as user_firstname');
        $select[] = DB::raw('users.lastname as user_lastname');
        $select[] = DB::raw('users.patronymic as user_patronymic');
//        $select[] = DB::raw('concat(users.firstname, users.lastname, users.patronymic) as user_fio');
        $select[] = DB::raw('job_categories.id as job_category_id');
        $select[] = DB::raw('job_categories.name as job_category_name');
        $select[] = DB::raw('users.taxpayer_registred_as_npd as taxpayer_registred_as_npd');

        $select[] = DB::raw('companies.id as company_id');
        $select[] = DB::raw('companies.name as company_name');
        $select[] = DB::raw('companies.inn as company_inn');

        $payouts = $payouts->whereIn('projects.id', $companyProjectsIds)->select($select);

        if (isset($filter['contractor_search'])) {
            $payouts->where(DB::raw('concat(users.firstname, users.lastname, users.patronymic, users.inn)'),
                'like',
                '%' . $filter['contractor_search'] . '%');
        }

        if (isset($filter['date_from'])) {
            $dateFrom = Carbon::createFromFormat('d.m.Y', $filter['date_from'])
                ->format('Y-m-d');

            $payouts = $payouts
                ->where('payouts.created_at', '>=', $dateFrom);
        }

        if (isset($filter['statuses'])) {
            $payouts->whereIn('payouts.status', $filter['statuses']);
        }

        if (isset($filter['date_till'])) {
            $date_till = Carbon::createFromFormat('d.m.Y', $filter['date_till'])
                ->format('Y-m-d');

            $payouts = $payouts
                ->where('payouts.created_at', '<=', $date_till);
        }

        return $payouts;
    }

    public function getCompanyPayoutsDatatable(Company $company, array $filter=[])
    {
        $payoutQuery = $this->getPayoutsQuery($company, $filter);

        $dataTable = DataTables::eloquent($payoutQuery);

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

        $dataTable = $dataTable->addColumn('project_id', function (Payout $payout) {
            return $payout->project_id;
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

        $dataTable = $dataTable->addColumn('project_name', function (Payout $payout) {
            return $payout->project_name;
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
            'project_id',
            'project_name',
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

        return $dataTable->smart(true);
    }

    public function downloadReceipts(Company $company, array $filter=[], array $ids=null)
    {
        $payoutQuery = Payout::query();

        if ($ids) {
            $payoutQuery->findMany($ids);
        } else {
            $payoutQuery = $this->getPayoutsQuery($company, $filter);
        }

        $payouts = $payoutQuery->whereNotNull('receipt_url')->get();

        if (empty($payouts->toArray()))
            throw new \Exception('Не найдено чеков для выбранных задач');

        $payoutsIds = $payouts->pluck('receipt_url', 'id');

        $payoutsReceipts = [];

        $client = new Client();

        $promises = [];

        foreach ($payoutsIds as $payoutsId=>$payoutUrl) {
            if (! is_string($payoutUrl))
                continue;

            $promise = $client->requestAsync('GET', $payoutUrl);
            $promise->then(function(\GuzzleHttp\Psr7\Response $response) use (&$payoutsReceipts, $payoutsId) {
                $payoutsReceipts[$payoutsId] = $response->getBody()->getContents();
            });

            $promises[] = $promise;
        }

        \GuzzleHttp\Promise\Utils::all($promises)->wait();

        $archive = ArchiveStream::instance_by_useragent(rand());

        ob_start();

        foreach ($payoutsReceipts as $payoutId=>$receipt) {
            try {
                $archive->add_file($payoutId . '.png', $receipt);
            } catch (Throwable $e) {
                throw new \Exception('File not found');
            }

            ob_flush();
            flush();
            ob_clean();
        }

        $archive->finish();
    }

    public function repay(Payout $payout)
    {
        if (array_search($payout->status, $this->getAllowedToRepayStatuses()) === false)
            throw new \Exception('Платеж не может быть повторен');

        $task = $payout->task;
        $payout->delete();

        StartTaskPaymentProcessJob::dispatchSync($task);
    }
}
