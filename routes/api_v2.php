<?php

use App\Http\Controllers\CompanyBankAccount\CompanyBankAccountController;
use App\Http\Controllers\PaymentMethods\CompanyPaymentMethodsController;
use App\Http\Controllers\PaymentMethods\ContractorPaymentMethodsController;
use App\Http\Controllers\Permissions\PermissionsController;
use App\Http\Controllers\Roles\RolesController;
use App\Http\Controllers\SignMe\SignMeController;
use App\Http\Controllers\Task\TaskPaymentsController;
use App\Http\Controllers\TinkoffContractorController;
use App\Http\Controllers\User\EmployeesController;
use App\Http\Controllers\UserBankAccount\UserBankAccountController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Order\OrderController;

use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Task\DataTableController as TasksDataTableController;

use App\Http\Controllers\Offer\OfferController;

use App\Http\Controllers\Payout\PayoutController;
use App\Http\Controllers\Payout\DataTableController as PayoutsDataTableController;


use App\Http\Controllers\Document\DocumentController;

/*
|--------------------------------------------------------------------------
| auth.jwt Routes
|--------------------------------------------------------------------------
|
| Here is where you can register auth.jwt routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "auth.jwt" middleware group. Enjoy building your auth.jwt!
|
*/

/**
  UserJobCallback::dispatch($me, CallbackSerialize::call(function (User $user) {
            Log::channel('debug')->debug('async call', ['test']);
        }, $me));
 */
Route::group(
    [
        'prefix' => 'company',
        'middleware' => ['auth.jwt', 'has_company']
    ],
    function () {
        Route::get('/receipts/download', [PayoutController::class, 'download'])->middleware('can:company.receipts.show');

        //ПЕРЕНС В КОНТРАКТОРА
        // Route::get('/{company_id}', [CompanyController::class, 'get'])
        //     ->where(['company_id' => '[0-9]+']);

        Route::patch('/me', [UserController::class, 'updateEmployeePersonalData']);

        // TODO нужен ли?
        //        Route::post('/', [CompanyController::class, 'updateProfile'])->middleware('auth.jwt');
        Route::post('/me/change_password', [UserController::class, 'changePassword']);


        Route::get('/by_inn/{inn}', [CompanyController::class, 'getDataByInn'])
            ->withoutMiddleware('has_company');

        Route::get('/permissions', [PermissionsController::class, 'index']);


        Route::post('/projects/{project_id}/attachUsers', [UserController::class, 'assignToUsers']);
        Route::post('/projects/{project_id}/detachUser', [UserController::class, 'detachUser']);

        Route::post('/users/{user_id}/attachProjects', [UserController::class, 'assignToProjects'])->can('company.admin');

        Route::get('/contractors/datatable', [UserController::class, 'datatable']);


        Route::get('/payment_methods', [CompanyPaymentMethodsController::class, 'index']);

        Route::group(
            [
                'prefix' => 'projects'
            ],
            function () {
                Route::post('/', [ProjectController::class, 'create']); //->middleware('is_identified');

                Route::get('/', [ProjectController::class, 'list']);

                //                Route::get('/', [ProjectController::class, 'getCompanyProjects']);

                Route::get('/{project_id}', [ProjectController::class, 'get'])
                    ->where(['project_id' => '[0-9]+']);

                Route::get('/{project_id}/documents/datatable', [DocumentController::class, 'datatable']);


                Route::get('/datatable', [ProjectController::class, 'clientProjectsDatatable'])
                    ->middleware('can:company.projects.show');


                Route::delete('/{project_id}/tasks/{task_id}', [TaskController::class, 'delete'])
                    ->middleware('can:company.tasks.delete', 'has_access_to_task');

                //TODO разделить создание и сохранение
                Route::post('/{project_id}/tasks/{task_id}', [TaskController::class, 'save'])
                    ->middleware('can:company.tasks.update');


                Route::get('/tasks/{task_id}/offers', [TaskController::class, 'offers'])
                    ->middleware('has_access_to_task');

                Route::post('/tasks/{task_id}/copy', [TaskController::class, 'copy'])
                    ->middleware('is_identified', 'can:company.tasks.update', 'has_access_to_task');

                Route::post('/tasks/{task_id}/pay', [TaskController::class, 'pay'])
                    ->middleware('is_identified', 'can:company.tasks.pay', 'has_access_to_task');


                Route::post('/tasks/{task_id}/confirm', [TaskController::class, 'confirm'])
                    ->middleware('has_access_to_task', 'is_identified');


                Route::post('/offers/{offer_id}/accept', [OfferController::class, 'accept'])
                    ->middleware('can:company.tasks.contractor_assign', 'is_identified');





                Route::post('/{project_id}/document', [DocumentController::class, 'create'])
                    ->middleware('is_identified', 'can:company.documents.create');

                Route::get('/{project_id}/contractors/datatable', [UserController::class, 'datatable']);

                Route::post('/{project_id}/order/simulate', [OrderController::class, 'simulate'])
                    ->middleware('has_access_to_project');
                Route::post('/{project_id}/upload_tasks', [OrderController::class, 'new'])
                    ->middleware(
                        //'is_identified',
                        'has_access_to_project'
                    );

                Route::get('/{project_id}/tasks/datatable', [TasksDataTableController::class, 'datatable'])
                    ->middleware('has_access_to_project');

                Route::get('/{project_id}/payouts/datatable', [PayoutsDataTableController::class, 'datatable']);
            }
        );


        Route::group(
            [
                'prefix' => 'payouts'
            ],
            function () {
                Route::get('/', [PayoutController::class, 'payouts'])
                    ->middleware('can:company.payouts.show');

                Route::post('/simulate', [PayoutController::class, 'simulate'])
                    ->middleware('is_identified');

                Route::post('/mass_pay', [PayoutController::class, 'massPay'])
                    ->middleware('is_identified');

                Route::post('/{payout_id}/repay', [PayoutController::class, 'repay'])
                    ->middleware('is_identified', 'can:company.payouts.repay');

                //TODO нужен ли???
                Route::get('/info/{payout_id}', [PayoutController::class, 'infoPayout'])
                    ->middleware('can:company.payouts.show');

                Route::get('/datatable', [PayoutsDataTableController::class, 'datatableAll'])
                    ->middleware('can:company.payouts.show');
            }
        );


        Route::group(
            [
                'prefix' => 'bank_account'
            ],
            function () {
                Route::post('/', [CompanyBankAccountController::class, 'store'])
                    ->middleware('can:company.bank_account.update');
                Route::get('/', [CompanyBankAccountController::class, 'get'])
                    ->middleware('can:company.bank_account.update');
            }
        );



        Route::group(
            [
                'prefix' => 'documents',

            ],
            function () {
                Route::get('/', [DocumentController::class, 'documents'])
                    ->middleware('can:company.documents.show');

                Route::get('/get', [DocumentController::class, 'getMany'])
                    ->name('documents.get-many')
                    ->middleware('can:company.documents.show');

                Route::get('/datatable', [DocumentController::class, 'datatable'])
                    ->middleware('can:company.documents.show');

                Route::get('/projects/datatable', [DocumentController::class, 'clientProjectsDatatable'])
                    ->middleware('can:company.documents.create');

                // TODO нужен ли?
                // нужен
                Route::get('/get/all', [DocumentController::class, 'getAll'])
                    ->name('documents.get-many')
                    ->middleware('can:company.documents.show');


                Route::get('/request_sign_documents', [DocumentController::class, 'requestSignDocuments'])
                    ->middleware('can:company.documents.request_sign');

                Route::post('/request_sign_documents_scope', [DocumentController::class, 'requestSignDocumentsInScope']);

                Route::post('/upload', [DocumentController::class, 'upload'])
                    ->middleware('is_identified', 'can:company.documents.create');

                Route::get('/{document_id}/download', [DocumentController::class, 'get'])
                    ->name('document.get');

                Route::get('/unrequested_signs_count', [DocumentController::class, 'unrequestedSignsCount'])
                    ->middleware('can:company.documents.request_sign');
            }
        );


        Route::group(
            [
                'prefix' => 'employees',
                'middleware' => 'can:company.admin'
            ],
            function () {
                Route::get('/', [EmployeesController::class, 'index']);
                Route::get('/{employee_id}', [EmployeesController::class, 'employee']);
                Route::post('/', [EmployeesController::class, 'store']);
                Route::delete('/{user_id}', [EmployeesController::class, 'destroy']);
            }
        );




        Route::group(
            [
                'prefix' => 'tasks',
                'middleware' => ['has_access_to_task']
            ],
            function () {
                Route::get('/{task_id}', [TaskController::class, 'get']);

                Route::post('/{task_id}/assignPaymentMethod', [TaskPaymentsController::class, 'assignCompanyPaymentMethod']);


                Route::post('/{task_id}/complete', [TaskController::class, 'clientComplete'])
                    ->middleware('can:company.tasks.accept_job');

                Route::get('/{tasks_group}/datatable', [TasksDataTableController::class, 'datatable']);

                Route::post('/{task_id}/return', [TaskController::class, 'return'])
                    ->middleware('is_identified', 'can:company.tasks.accept_job', 'has_access_to_task');

                Route::post('/{task_id}/invite_user/{user_id}', [TaskController::class, 'inviteUser'])
                    ->middleware('can:company.tasks.contractor_assign');
            }
        );



        Route::group(
            [
                'prefix' => 'signme'
            ],
            function () {
                Route::post('/register', [SignMeController::class, 'registerCompanyUser']);

                Route::get('/status', [SignMeController::class, 'updateIdentification']);

                Route::get('/state', [SignMeController::class, 'getSignMeState']);

                Route::get('/cert', [SignMeController::class, 'certData']);
            }
        );




        Route::group(
            [
                'prefix' => 'roles',
            ],
            function () {

                Route::get('/', [RolesController::class, 'index']);
                Route::get('/{role_id}', [RolesController::class, 'show']);
                Route::post('/', [RolesController::class, 'store']);
                Route::patch('/{role_id}', [RolesController::class, 'update']);
                Route::delete('/{role_id}', [RolesController::class, 'destroy']);
                Route::post('/{role_id}/assign/role', [RolesController::class, 'assignToRole']);
                Route::post('/assign/user/{user_id}', [RolesController::class, 'assignToUser']);
            }
        );
    }
);













Route::group(
    [
        'prefix' => 'contractor',
        'middleware' => ['auth.jwt']
    ],
    function () {

        Route::get('/projects/{project_id}/documents/datatable', [DocumentController::class, 'datatable']);

        Route::get('/', [UserController::class, 'get']);

        Route::post('/register', [UserController::class, 'registerContractor']);

        Route::patch('/personal_data', [UserController::class, 'updateContractorPersonalData']);

        Route::get('/payment_methods', [ContractorPaymentMethodsController::class, 'index']);

        Route::get('/company/{company_id}', [CompanyController::class, 'get'])
            ->where(['company_id' => '[0-9]+']);

        Route::group(
            [
                'prefix' => 'tasks',
                'middleware' => ['has_access_to_task']
            ],
            function () {
                Route::get('/{task_group}/datatable', [TasksDataTableController::class, 'contractorDataTable'])
                    ->withoutMiddleware('has_access_to_task');

                Route::post('/{task_id}/refuse', [TaskController::class, 'refuse']);

                Route::post('/{task_id}/assignPaymentMethod', [TaskPaymentsController::class, 'assignContractorPaymentMethod']);

                Route::post('/{task_id}/complete', [TaskController::class, 'complete'])
                    ->middleware('is_identified', 'is_npd');

                Route::get('/{task_id}', [TaskController::class, 'get']);

                Route::get('/', [TaskController::class, 'getUserTasks']);


                Route::post('/{task_id}/make_offer', [TaskController::class, 'makeOffer']);

                Route::get('/{task_id}/my_offer', [TaskController::class, 'myOffer']);


                Route::post('/{task_id}/confirm/accept', [TaskController::class, 'acceptPrice'])
                    ->withoutMiddleware('has_access_to_task')
                    ->middleware('is_identified');

                Route::post('/{task_id}/confirm/deny', [TaskController::class, 'acceptDeny'])
                    ->withoutMiddleware('has_access_to_task')
                    ->middleware('is_identified');
            }
        );



        Route::group(
            [
                'prefix' => 'signme'
            ],
            function () {
                Route::post('/register', [SignMeController::class, 'registerContractor']);

                Route::get('/status', [SignMeController::class, 'updateIdentification']);

                Route::get('/state', [SignMeController::class, 'getSignMeState']);

                Route::get('/cert', [SignMeController::class, 'certData']);
            }
        );



        Route::group(
            [
                'prefix' => 'bank_account'
            ],
            function () {
                Route::post('/', [UserBankAccountController::class, 'store']);
                Route::get('/', [UserBankAccountController::class, 'get']);
            }
        );



        Route::group(
            [
                'prefix' => 'documents'
            ],
            function () {
                Route::get('/', [DocumentController::class, 'documents']);

                // TODO нужен ли
                // нужен
                Route::get('/get/all', [DocumentController::class, 'getAll']);


                Route::get('/get', [DocumentController::class, 'getMany']);

                Route::get('/datatable', [DocumentController::class, 'datatable']);

                Route::get('/{document_id}/download', [DocumentController::class, 'get']);
            }
        );


        Route::group(
            [
                'prefix' => 'bank_accounts'
            ],
            function () {
                Route::group(
                    [
                    'prefix' => 'tinkoff'
                    ],
                    function () {

                        Route::post('/', [TinkoffContractorController::class, 'addCard']);

                        Route::post('webhooks/card_bind_result', [TinkoffContractorController::class, 'cardBindResult']);
                    }
                );


                Route::group(
                    [
                        'prefix' => 'mobi'
                    ],
                    function () {

                    }
                );
            }
        );



        Route::group(
            [
                'prefix' => 'npd'
            ],

            function () {
                Route::post('/bind_to_partner', [UserController::class, 'bindToPartner']);



                Route::post('/unbind_from_partner', [UserController::class, 'unbindFromPartner']);



                Route::post('/check_binding', [UserController::class, 'updateBinding'])
                    ->middleware('throttle:3,1');



                Route::post('/cancel_bind_to_partner', [UserController::class, 'cancelBindToPartner']);
            }
        );



        Route::group(
            [
                'prefix' => 'payouts'
            ],
            function () {
                Route::get('/datatable', [PayoutsDataTableController::class, 'datatable']);


                Route::get('/{payout_id}/info', [PayoutController::class, 'info']);


                Route::post('/{payout_id}/annulate', [PayoutController::class, 'annulate']);
            }
        );
    }
);
