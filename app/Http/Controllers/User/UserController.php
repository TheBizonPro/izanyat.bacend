<?php

namespace App\Http\Controllers\User;

use App\Exceptions\UserRegistrationExeption;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignProjectToUsersRequest;
use App\Http\Requests\AssignUserToProjectRequest;
use App\Http\Requests\RegisterContractorRequest;
use App\Http\Requests\UpdatePersonalDataRequest;
use App\Jobs\FNS\CheckTaxpayerBindStatus as FNSCheckTaxpayerBindStatus;
use App\Services\LogsService;
use App\Services\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;

use Yajra\DataTables\Facades\DataTables;
use DB;
use Hash;
use Auth;


use App\Jobs\FNS\BindTaxpayer as FNSBindTaxpayer;
use App\Jobs\FNS\UnbindTaxpayer as FNSUnbindTaxpayer;
use App\Http\Resources\ContractorResource;

use App\Jobs\SignMe\CheckIdentification;
use App\Jobs\FNS\UpdateTaxpayerData as FNSUpdateTaxpayerData;
use App\Jobs\SendNotificationJob;
use App\Jobs\UserJobCallback;
use App\Services\Adapters\CallbackSerialize;

class UserController extends Controller
{
    protected UsersService $usersService;
    protected LogsService $logsService;

    /**
     * @param UsersService $usersService
     * @param LogsService $logsService
     */
    public function __construct(UsersService $usersService, LogsService $logsService,)
    {
        $this->usersService = $usersService;
        $this->logsService = $logsService;
    }


    /**
     * Регистрация пользователя
     */
    public function registration(Request $request)
    {

        try {
            $this->usersService->registerUser($request->all(), Auth::user());
        } catch (UserRegistrationExeption $e) {
            return response()->json([
                'title' => 'Ошибка регистрации',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getCode(), [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'title' => 'Ошибка регистрации',
                'message' => $e->getMessage()
            ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные сохранены',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }

    public function registerContractor(RegisterContractorRequest $request)
    {
        $user = $request->user();
        $userData = array_merge([
            'is_selfemployed' => 1
        ], $request->validated());

        $user->update($userData);


        $profileLink = $user->is_selfemployed ? '/my' : '/my-company';


        SendNotificationJob::dispatch(
            $user,
            'Регистрация прошла успешно',
            "Для получения или отправки выплат необходимо перейти в <a href='$profileLink'>профиль</a> и заполнить данные для платежей "
        );
        SendNotificationJob::dispatch(
            $user,
            'Регистрация прошла успешно',
            "Поздравляем с успешной регистрацией на платформе ЯЗанят! Для начала работы вам необходимо привязать Мой Налог к нашему сервису, для этого перейдите в ваш профиль, выберите вкладку Интеграции, а затем Мой Налог и нажмите Выполнить привязку к Язанят"
        );
        $this->logsService->userLog('Пользователь получил уведомление о необходимости привязки к Мой Налог', $user->id,[]);

        return [
            'user' => $user
        ];
    }


    /**
     * Загрузка данных пользователя
     */
    public function me(Request $request)
    {
        $me = Auth::user();
        $me->load('company', 'roles');

        $response = [
            'me' => new ContractorResource($me),
            'company' => $me->company,
            'title' => 'Успешно',
            'message' => 'Данные пользователя получены'
        ];

        if ($request->get('withPermissions') == 1)
            $response['permissions'] = $me->getAllPermissions();

        if ($request->get('withRoles') == 1)
            $response['roles'] = $me->roles;

        if ($request->get('withSignme') == 1)
            $response['signme'] = $me->signMeState;

        if (
            $me->is_administrator == 0 and
            $me->is_client == 0 and
            $me->is_selfemployed == 0
        ) {
            return response()
                ->json([
                    'action' => 'registration',
                    'title' => 'Требуется действие',
                    'message' => 'Необходимо заполнить профиль'
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json($response, 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function updateBinding(Request $request)
    {
        $user = $request->user();

        $status = FNSCheckTaxpayerBindStatus::dispatchNow($user->taxpayer_bind_id);

        if ($status === 'COMPLETED') {
            $user->taxpayer_registred_as_npd = true;
            $user->taxpayer_binded_to_platform = true;
            $user->taxpayer_bind_id = null;

            $this->logsService->userLog('Пользователь подтвердил привязку к "Мой Налог"', $user->id, []);

            FNSUpdateTaxpayerData::dispatch($user);
        } else if ($status === 'FAILED') {
            $user->taxpayer_registred_as_npd = null;
            $user->taxpayer_binded_to_platform = false;
            $user->taxpayer_bind_id = null;

            $this->logsService->userLog('Пользователь отменил привязку в "Мой Налог"', $user->id, []);
        }

        $user->save();

        return [
            'user' => $user->toArray(),
            'status' => $status
        ];
    }


    /**
     * Смена пароля
     */
    public function changePassword(Request $request)
    {
        $me = Auth::user();

        if ($request->password == null) {
            return response()
                ->json([
                    'error_code' => 'invalid_password',
                    'title' => 'Ошибка',
                    'message' => 'Не передан новый пароль!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        // if ($me->phone_code != $request->code) {
        //     return response()
        //         ->json([
        //             'error_code' => 'invalid_code',
        //             'title' => 'Ошибка',
        //             'message' => 'Неверный смс-код!'
        //         ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        // }

        if (mb_strlen($request->password) < 6) {
            return response()
                ->json([
                    'error_code' => 'weak_password',
                    'title' => 'Ошибка',
                    'message' => 'Пароль должен быть больше 5 символов'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $me->password = Hash::make($request->password);
        $me->phone_code = null;
        $me->save();

        return response()
            ->json([
                'action' => 'redirect',
                'title' => 'Успешно',
                'message' => 'Пароль успешно изменен!'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    /**
     * Вывод данных пользователей (самозанятых) в виде DataTables
     */
    public function datatable(Request $request)
    {
        $me = Auth::user();
        $users = User::where('is_selfemployed', '=', true);
        $users = $users->where('is_identified', '=', true);

        $users = $users->leftJoin(DB::raw('job_categories as users_job_categories'), 'users_job_categories.id', '=', 'users.job_category_id');

        $select = [];
        $select[] = DB::raw('users.id as id');
        $select[] = DB::raw('users.email as email');
        $select[] = DB::raw('users.phone as phone');
        $select[] = DB::raw('users.inn as inn');
        $select[] = DB::raw('users.firstname as firstname');
        $select[] = DB::raw('users.lastname as lastname');
        $select[] = DB::raw('users.patronymic as patronymic');
        $select[] = DB::raw('users.birth_date as birth_date');
        $select[] = DB::raw('users.passport_series as passport_series');
        $select[] = DB::raw('users.passport_number as passport_number');
        $select[] = DB::raw('users.snils as snils');
        $select[] = DB::raw('users.rating as rating');
        //        $select[] = DB::raw('users.signme_id as signme_id');
        $select[] = DB::raw('users.taxpayer_registred_as_npd as taxpayer_registred_as_npd');
        $select[] = DB::raw('users.job_category_id as job_category_id');
        $select[] = DB::raw('users_job_categories.name as job_category_name');

        if ($request->filter) {

            if (Arr::exists($request->filter, 'job_category_id')) {
                $users = $users
                    ->where('users.job_category_id', '=', $request->filter['job_category_id']);
            }

            if (Arr::exists($request->filter, 'inn')) {
                $users = $users
                    ->whereRaw('users.inn like ?', ["{$request->filter['inn']}%"]);
            }

            if (Arr::exists($request->filter, 'lastname')) {
                $users = $users
                    ->whereRaw('users.lastname like ?', ["{$request->filter['lastname']}%"]);
            }
        }


        /*	if ($request->project_id) {
			$project = Project::where('id', '=', $request->project_id)->first();

			$users = $users
				->rightJoin('projects_users', 'projects_users.user_id', '=', 'users.id')
				->leftJoin(DB::raw('job_categories as projects_users_job_categories'), 'projects_users_job_categories.id', '=', 'projects_users.job_category_id')
				->leftJoin('documents', 'documents.id', '=', 'projects_users.document_id')
				->where('projects_users.project_id', '=', $project->id);

			$select[]= DB::raw('projects_users.accepted_by_user as project_user_accepted_by_user');
			$select[]= DB::raw('projects_users.user_id as project_user_user_id');
			$select[]= DB::raw('projects_users.project_id as project_user_project_id');
			$select[]= DB::raw('projects_users.job_category_id as project_job_category_id');
			$select[]= DB::raw('projects_users_job_categories.name as project_job_category_name');
			$select[]= DB::raw('documents.id as contract_document_id');
			$select[]= DB::raw('documents.company_sign_requested as contract_document_company_sign_requested');
			$select[]= DB::raw('documents.user_sign_requested as contract_document_user_sign_requested');
			$select[]= DB::raw('documents.company_sig as contract_document_company_sig');
			$select[]= DB::raw('documents.user_sig as contract_document_user_sig');


			if ($request->filter) {
				if (Arr::exists($request->filter, 'project_job_category_id')) {
					$users = $users
						->whereRaw('projects_users.job_category_id like ?', ["{$request->filter['project_job_category_id']}%"]);
				}
			}
		}*/


        $users = $users->select($select);

        $dataTable = DataTables::eloquent($users);


        $dataTable = $dataTable->addColumn('id', function (User $user) {
            return $user->id;
        });

        $dataTable = $dataTable->addColumn('name', function (User $user) {
            return $user->name;
        });
        $dataTable = $dataTable->filterColumn('name', function ($query, $keyword) {
            $query->whereRaw('lastname like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('name', function ($query, $order) {
            $query->orderBy('lastname', $order);
        });


        $dataTable = $dataTable->addColumn('phone', function (User $user) {
            return $user->phone;
        });
        $dataTable = $dataTable->filterColumn('phone', function ($query, $keyword) {
            $query->whereRaw('phone like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('phone', function ($query, $order) {
            $query->orderBy('phone', $order);
        });


        $dataTable = $dataTable->addColumn('inn', function (User $user) {
            return $user->inn;
        });
        $dataTable = $dataTable->filterColumn('inn', function ($query, $keyword) {
            $query->whereRaw('inn like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('inn', function ($query, $order) {
            $query->orderBy('inn', $order);
        });


        $dataTable = $dataTable->addColumn('job_category_name', function (User $user) {
            return $user->job_category_name;
        });
        $dataTable = $dataTable->filterColumn('job_category_name', function ($query, $keyword) {
            $query->whereRaw('users_job_categories.name like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('job_category_name', function ($query, $order) {
            $query->orderBy('users_job_categories.name', $order);
        });

        $dataTable = $dataTable->addColumn('taxpayer_registred_as_npd', function (User $user) {
            return $user->taxpayer_registred_as_npd;
        });
        $dataTable = $dataTable->orderColumn('taxpayer_registred_as_npd', function ($query, $order) {
            $query->orderBy('taxpayer_registred_as_npd', $order);
        });


        if ($request->project_id) {

            $dataTable = $dataTable->addColumn('project_user_accepted_by_user', function (User $user) {
                return $user->project_user_accepted_by_user;
            });

            $dataTable = $dataTable->addColumn('project_job_category_name', function (User $user) {
                return $user->project_job_category_name;
            });
            $dataTable = $dataTable->filterColumn('project_job_category_name', function ($query, $keyword) {
                $query->whereRaw('projects_users_job_categories.name like ?', ["%{$keyword}%"]);
            });
            $dataTable = $dataTable->orderColumn('project_job_category_name', function ($query, $order) {
                $query->orderBy('projects_users_job_categories.name', $order);
            });

            $dataTable = $dataTable->addColumn('status', function (User $user) {
                if ($user->contract_document_id == null) {
                    return "no_contract";
                } else if ($user->contract_document_company_sig == null or $user->contract_document_user_sig == null) {
                    return "not_signed";
                } else if ($user->contract_document_company_sig != null and $user->contract_document_user_sig != null) {
                    return "signed";
                }
            });
        }


        $dataTable = $dataTable->addColumn('signme_id', function (User $user) {
            return $user->signme_id;
        });
        $dataTable = $dataTable->orderColumn('signme_id', function ($query, $order) {
            $query->orderBy('signme_id', $order);
        });

        $dataTable = $dataTable->addColumn('birth_date', function (User $user) {
            return $user->birth_date;
        });
        $dataTable = $dataTable->filterColumn('birth_date', function ($query, $keyword) {
            $query->whereRaw('birth_date like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('birth_date', function ($query, $order) {
            $query->orderBy('birth_date', $order);
        });


        $dataTable = $dataTable->addColumn('passport', function (User $user) {
            return $user->passport_series . ' ' . $user->passport_number;
        });
        $dataTable = $dataTable->filterColumn('passport', function ($query, $keyword) {
            $query->whereRaw('concat(passport_series, " ", passport_number) like ?', ["%{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('passport', function ($query, $order) {
            $query->orderBy('passport_series', $order);
        });


        $dataTable = $dataTable->addColumn('rating', function (User $user) {
            return floatval($user->rating);
        });
        $dataTable = $dataTable->filterColumn('rating', function ($query, $keyword) {
            $query->whereRaw('rating like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('rating', function ($query, $order) {
            $query->orderBy('rating', $order);
        });

        $dataTable = $dataTable->addColumn('snils', function (User $user) {
            return $user->snils;
        });
        $dataTable = $dataTable->filterColumn('snils', function ($query, $keyword) {
            $query->whereRaw('snils like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('snils', function ($query, $order) {
            $query->orderBy('snils', $order);
        });


        $dataTable = $dataTable->addColumn('address', function (User $user) {
            return $user->address;
        });
        $dataTable = $dataTable->filterColumn('address', function ($query, $keyword) {
            $query->whereRaw('address like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('address', function ($query, $order) {
            $query->orderBy('address', $order);
        });


        $dataTable = $dataTable->addColumn('email', function (User $user) {
            return $user->email;
        });
        $dataTable = $dataTable->filterColumn('email', function ($query, $keyword) {
            $query->whereRaw('email like ?', ["{$keyword}%"]);
        });
        $dataTable = $dataTable->orderColumn('email', function ($query, $order) {
            $query->orderBy('email', $order);
        });


        $dataTable = $dataTable->addColumn('created_date', function (User $user) {
            return Carbon::parse($user->created_at)->format('d.m.Y');
        });
        $dataTable = $dataTable->orderColumn('created_date', function ($query, $order) {
            $query->orderBy('created_date', $order);
        });

        $only = [
            'id',
            'name',
            'firstname',
            'lastname',
            'patronymic',
            'rating',
            'phone',
            'inn',
            'job_category_name',
            'project_job_category_name',
            'status',
            'birth_date',
            'passport',
            'snils',
            'taxpayer_registred_as_npd',
            'project_user_accepted_by_user',
            'signme_id',
            'address',
            'email',
            'created_date',
        ];

        $dataTable = $dataTable->only($only);

        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }



    /**
     * Привязка к партнеру в ПП НПД
     */
    public function bindToPartner(Request $request)
    {
        $me = Auth::user();
        if ($me->is_selfemployed == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Привязка к партнеру в ПП НПД доступна только для самозанятых!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $this->logsService->fnsLog('Пользователь начал привязку к "Мой Налог"', $me->id, []);

        $errorText = '';
        try {
            $bindStatus = FNSBindTaxpayer::dispatchNow($me);
            $this->logsService->fnsLog('Получили результат привязки к "Мой Налог"', $me->id, [
                'bind_status' => $bindStatus
            ]);
        } catch (\Throwable $e) {
            $bindStatus = 'error';
            $error = $e->getMessage();

            $this->logsService->fnsLog('Ошибка привязки к "Мой Налог"', $me->id, [
                'error' => $e->getMessage()
            ]);

            if (mb_strpos($error, 'TAXPAYER_UNREGISTERED') !== false) {

                $errorText = 'Вы не зарегистрированы как плательщик налога на проффесиональный доход (НПД). Вам необходимо встать на учет в качестве НПД. Информация о том, как это сделать доступна в нашей <a href="/knowledge-base">Базе знаний</a>';
            } else {
                $errorText = 'Не удалось связаться с ФНС и выполнить привзяку к партнеру, повторите попытку через 5 минут. Ошибка: ' . $error;
            }
        }

        if ($bindStatus == 'bind_requested') {

            return response()
                ->json([
                    'title' => 'Успешно',
                    'message' => 'Запрос на привязку к партнеру отправлен! Перейдите в приложение «Мой Налог» и разрешите приложению «Я занят» доступ к данным!'
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        } else if ($bindStatus == 'already_bound') {
            /**
             * Сверка данных
             */
            try {
                $this->logsService->fnsLog('Начинаем сверку данных пользователя в ЯЗанят и "Мой Налог"', $me->id);
                FNSUpdateTaxpayerData::dispatch($me);
            } catch (\Throwable $e) {
            }

            return response()
                ->json([
                    'title' => 'Успешно',
                    'message' => 'Привязка к партнеру успешно выполнена. Вы можете работать в платформе: брать и выполнять задачи, получать оплату.'
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        } else if ($bindStatus == 'error') {

            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => $errorText
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
    }


    public function updateContractorPersonalData(UpdatePersonalDataRequest $request)
    {
        $user = $request->user();
        try {
            $user->updateContractorPersonalInfo($request->validated(), ['inn', 'email']);
        } catch (\Exception $e) {
            return \Response::json([
                'error' => $e->getMessage()
            ], 403);
        }

        $user->refresh();

        return [
            'user' => $user->toArray()
        ];
    }

    public function updateEmployeePersonalData(UpdatePersonalDataRequest $request)
    {
        $user = $request->user();
        //        dd($request->validated());
        try {
            $user->updateEmployeePersonalData($request->validated());
        } catch (\Exception $e) {
            return \Response::json([
                'error' => $e->getMessage()
            ], 403);
        }

        $user->refresh();

        return [
            'user' => $user->toArray()
        ];
    }

    public function assignToUsers(AssignProjectToUsersRequest $request, int $projectId)
    {
        $usersIds = $request['users'] ?? [];
        $project = Project::findOrFail($projectId);

        $project->users()->sync($usersIds);

        return [
            'message' => 'assigned'
        ];
    }

    public function detachUser(Request $request, int $projectId)
    {
        $userId = $request->employee_id;

        $project = Project::findOrFail($projectId);
        $projectName = $project->name;

        $filteredUsers =  $project->users->filter(fn (User $user) => $user->id !== (int) $userId);
        $filteredUsersIds = $project->users->filter(fn ($user) => $user->id !== (int) $userId)->pluck('id');

        $filteredUsers->each(function (User $user) use ($projectName) {
            SendNotificationJob::dispatch(
                $user,
                'Отстранение от проекта',
                "Вас отстранили от проекта $projectName"
            );
        });

        $project->users()->sync($filteredUsersIds);

        return [
            'message' => 'assigned'
        ];
    }

    public function assignToProjects(AssignUserToProjectRequest $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $projectsIds = $request['projects'] ?? [];

        $user->projects()->sync($projectsIds);

        return [
            'message' => 'assigned'
        ];
    }


    public function updateProfile(Request $request)
    {
        $me = Auth::user();

        $me->email = $request->email;
        $me->job_category_id = $request->job_category_id;
        $me->about = $request->about;
        $me->save();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Данные обновлены'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }





    /**
     * Отвязка от партнера в ПП НПД
     */
    public function unbindFromPartner(Request $request)
    {
        $me = Auth::user();
        if ($me->is_selfemployed == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Привязка/отвязка от партнера в ПП НПД доступна только для самозанятых!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $this->logsService->fnsLog('Получили запрос на отвязку от "Мой Налог"', $me->id);


        try {
            FNSUnbindTaxpayer::dispatchNow($me);
            $this->logsService->fnsLog('Успешно выполнили отвязку от "Мой Налог"', $me->id);
        } catch (\Throwable $e) {
            $this->logsService->fnsLog('Ошибка отвязки от "Мой Налог"', $me->id, [
                'message' => $e->getMessage()
            ]);
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Не удалось связаться с ФНС и выполнить отвязку от партнера, повторите попытку через 5 минут, или выполните отвязку в приложении «Мой Налог». Ошибка: ' . $e->getMessage()
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы успешно отвязаны от партнера.'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    public function cancelBindToPartner(Request $request)
    {
        $me = Auth::user();
        if ($me->is_selfemployed == false) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Привязка/отвязка от партнера в ПП НПД доступна только для самозанятых!'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        $this->logsService->userLog('Получили запрос на отмену привязки к "Мой Налог"', $me->id);

        //TODO сделать фактическую отвязку от моего налога т.е слать запрос в фнс, а не только у нас в базе обновлять

        $me->taxpayer_registred_as_npd = null;
        $me->taxpayer_binded_to_platform = false;
        $me->taxpayer_income_limit_not_exceeded = null;
        $me->taxpayer_bind_id = null;
        $me->save();

        $this->logsService->userLog('Отменили привязку к "Мой Налог"', $me->id);


        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы отменили привязку к партнеру'
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    public function get(Request $request)
    {
        $user = User::where('id', '=', $request->user_id)->firstOrFail();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Вы успешно отвязаны от партнера',
                'user' => new ContractorResource($user)
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Проверка статуса идентификации личности в SignMe
     */
    public function checkSignMeIdentification(Request $request)
    {
        $me = Auth::user();

        try {
            $is_identified = CheckIdentification::dispatchNow($me);
        } catch (\Throwable $e) {
            return response()
                ->json([
                    'title' => 'Ошибка проверки',
                    'message' => 'Не удалось выполнить проверку. ' . $e->getMessage(),
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        ///$is_identified = true;


        if ($is_identified === true) {

            $me->is_identified = true;
            $me->save();

            return response()
                ->json([
                    'title' => 'Идентификация пройдена',
                    'message' => 'Спасибо! Вы можете продолжить работу в системе!',
                ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        } else {

            return response()
                ->json([
                    'title' => 'Идентификация еще не пройдена',
                    'message' => 'По данным SignMe идентификация личности еще не пройдена',
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }
    }
}
