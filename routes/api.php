<?php

use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\CompanyBankAccount\CompanyBankAccountController;
use App\Http\Controllers\Permissions\PermissionsController;
use App\Http\Controllers\Roles\RolesController;
use App\Http\Controllers\User\EmployeesController;
use App\Http\Controllers\UserBankAccount\UserBankAccountController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Bank\BankController;
use App\Http\Controllers\Region\RegionController;
use App\Http\Controllers\JobCategory\JobCategoryController;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Notification\NotificationController;
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


Route::any('/test-auth', [AuthController::class, 'testAuth']);
Route::post('/mobile/login', [AuthController::class, 'mobileAuth']);


Route::get('/check-auth', [AuthController::class, 'checkAuth']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/me', [UserController::class, 'me'])->middleware('auth.jwt');

Route::post('/me', [UserController::class, 'updateProfile'])->middleware('auth.jwt');



Route::post('/change_password', [UserController::class, 'changePassword'])->middleware('auth.jwt');



/**
 * Notifications
 */
Route::get('/notifications/datatable', [NotificationController::class, 'datatable'])
    ->middleware('auth.jwt');

Route::get('/notifications', [NotificationController::class, 'notifications'])
    ->middleware('auth.jwt');

Route::get('/notifications/unread_count', [NotificationController::class, 'unreadCount'])
    ->middleware('auth.jwt');

Route::get('/notification/{notification_id}', [NotificationController::class, 'get'])
    ->middleware('auth.jwt');

Route::post('notifications/read_all', [NotificationController::class, 'readAll'])
    ->middleware('auth.jwt');

/**
 * Projects
 */
//Route::get('/projects', [ProjectController::class, 'list'])
//    ->middleware('auth.jwt');

//Route::get('/contractor/projects/datatable', [ProjectController::class, 'contractorProjectsDatatable'])
//    ->middleware('auth.jwt');

//Route::get('/client/projects/datatable', [ProjectController::class, 'clientProjectsDatatable'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::get('/client/projects', [ProjectController::class, 'getCompanyProjects'])->middleware('auth.jwt');



//Route::get('/project/{project_id}', [ProjectController::class, 'get'])->middleware('auth.jwt');

//Route::post('/project', [ProjectController::class, 'create'])->middleware('auth.jwt')->middleware('is_identified');



//Route::post('/companies/{company_id}/bank_account', [CompanyBankAccountController::class, 'store'])
//    ->middleware('auth.jwt', 'can:company.bank_account.update');
//Route::get('/companies/{company_id}/bank_account', [CompanyBankAccountController::class, 'get'])
//    ->middleware('auth.jwt', 'can:company.bank_account.update');


//Route::get('/user/{user_id}', [UserController::class, 'get'])->middleware('auth.jwt');

// Создание/обновление банковских реквизитов пользователя
//Route::post('/users/{user_id}/bank_account', [UserBankAccountController::class, 'store']);
//Route::get('/users/{user_id}/bank_account', [UserBankAccountController::class, 'get']);

/**
 * Users in project
 */
/*Route::post('/project/{project_id}/contractors', [ProjectController::class, 'addContractors'])
	->middleware('auth:sanctum');

Route::post('/project/{project_id}/accept', [ProjectController::class, 'acceptProject'])
	->middleware('auth:sanctum');

Route::post('/project/{project_id}/decline', [ProjectController::class, 'declineProject'])
	->middleware('auth:sanctum');

*/

//Route::get('/contractors/datatable', [UserController::class, 'datatable'])->middleware('auth.jwt');



//Route::get('/project/{project_id}/contractors/datatable', [UserController::class, 'datatable'])->middleware('auth.jwt');



//Route::get('/project/{project_id}/orders', [OrderController::class, 'list'])->middleware('auth.jwt', 'has_access_to_project');

//Route::post('/project/{project_id}/order/simulate', [OrderController::class, 'simulate'])->middleware('auth.jwt')->middleware('is_identified', 'has_access_to_project');

//Route::post('/project/{project_id}/upload_tasks', [OrderController::class, 'new'])->middleware('auth.jwt')->middleware('is_identified', 'has_access_to_project');

// TODO а нужен ли этот роут????????
Route::get('/documents/projects/list', [ProjectController::class, 'projectList'])
    ->middleware('auth.jwt');

Route::post('/document/projects/upload', [DocumentController::class, 'createDocument'])
    ->middleware('auth.jwt');
/**
 * Таблца задач
 */
//Route::get('/project/{project_id}/tasks/datatable', [TasksDataTableController::class, 'datatable'])->middleware('auth.jwt', 'has_access_to_project');

/**
 * Таблца задач
 */
//Route::get('/tasks/{tasks_group}/datatable', [TasksDataTableController::class, 'datatable'])->middleware('auth.jwt');

//TODO это эндпоинт для мобильного приложения, надо вынести их в отдельный файл роутов
Route::get('/tasks', [TaskController::class, 'getAll'])->middleware('auth.jwt');

//TODO аналогично что и сверху
Route::get('/users/{user_id}/tasks', [TaskController::class, 'getUserTasks'])->middleware('auth.jwt');

/**
 * Сделать оффер (от лица)
 */
//Route::post('/task/{task_id}/make_offer', [TaskController::class, 'makeOffer'])
//	->middleware('auth:sanctum')->middleware(['is_identified', 'is_npd']);
// делаем так, что НП НПД быть не обязательно, возможно временно
//Route::post('/task/{task_id}/make_offer', [TaskController::class, 'makeOffer'])->middleware('auth.jwt');

//Route::post('/client/task/{task_id}/complete', [TaskController::class, 'clientComplete'])
//    ->middleware('auth.jwt', 'can:company.tasks.accept_job');

/**
 * Получение задачи
 */
//Route::get('/task/{task_id}', [TaskController::class, 'get'])
//    ->middleware(['auth.jwt', 'has_access_to_task']);

/**
 * Получение моего офера задачи
 */
//Route::get('/task/{task_id}/my_offer', [TaskController::class, 'myOffer'])
//    ->middleware('auth.jwt', 'has_access_to_task');

/**
 * Завершение задачи
 */
//Route::post('/task/{task_id}/complete', [TaskController::class, 'complete'])
//    ->middleware('auth.jwt', 'is_identified', 'is_npd', 'has_access_to_task');

/**
 * Отказ от выполнения задачи
 */
//Route::post('/task/{task_id}/refuse', [TaskController::class, 'refuse'])->middleware('auth.jwt');

/**
 * Удаление задачи
 */
//Route::delete('/task/{task_id}', [TaskController::class, 'delete'])
//    ->middleware('auth.jwt', 'is_identified', 'can:company.tasks.delete', 'has_access_to_task');

/**
 * Сохранение задачи
 */
//Route::post('/project/{project_id}/task/{task_id}', [TaskController::class, 'save'])
//    ->middleware('auth.jwt', 'is_identified', 'can:company.tasks.update', 'has_access_to_task');

/**
 * Оферы по задаче
 */
//Route::get('/task/{task_id}/offers', [TaskController::class, 'offers'])->middleware('auth.jwt', 'has_access_to_task');

/**
 * Копировать задачу
 */
//Route::post('/task/{task_id}/copy', [TaskController::class, 'copy'])
//    ->middleware('is_identified', 'auth.jwt', 'can:company.tasks.update', 'has_access_to_task');

/**
 * Принять Офер
 */

//Route::post('/offer/{offer_id}/accept', [OfferController::class, 'accept'])
//    ->middleware('auth.jwt', 'can:company.tasks.contractor_assign', 'is_identified');

/**
 * Оплата задачи
 */

//Route::post('/task/{task_id}/pay', [TaskController::class, 'pay'])
//    ->middleware('is_identified', 'auth.jwt', 'can:company.tasks.pay', 'has_access_to_task');

/**
 * Согласование оплаты
 */
//Route::post('/task/{task_id}/confirm', [TaskController::class, 'confirm'])
//    ->middleware('auth.jwt', 'has_access_to_task', 'is_identified');
//
//
//
//Route::post('/task/{task_id}/confirm/accept', [TaskController::class, 'acceptPrice'])
//    ->middleware('auth.jwt', 'has_access_to_task', 'is_identified');
//
//
//
//
//Route::post('/task/{task_id}/confirm/deny', [TaskController::class, 'acceptDeny'])
//    ->middleware('auth.jwt', 'has_access_to_task', 'is_identified');

/**
 * Оплата задачи
 */
//Route::post('/task/{task_id}/return', [TaskController::class, 'return'])
//    ->middleware('is_identified', 'auth.jwt', 'can:company.tasks.accept_job', 'has_access_to_task');

//Route::get('/receipts/download', [PayoutController::class, 'download'])->middleware('auth.jwt', 'can:company.receipts.show');

//Route::get('/client/payouts/datatable', [PayoutsDataTableController::class, 'datatableAll'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::get('/payouts/datatable', [PayoutsDataTableController::class, 'datatable'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::get('/payouts', [PayoutController::class, 'payouts'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::post('/payout/{payout_id}/annulate', [PayoutController::class, 'annulate'])->middleware('auth.jwt');

//Route::get('/payout/{payout_id}/info', [PayoutController::class, 'info'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::get('/payout/info/{payout_id}', [PayoutController::class, 'infoPayout'])
//    ->middleware('auth.jwt', 'can:company.payouts.show');

//Route::get('/project/{project_id}/payouts/datatable', [PayoutsDataTableController::class, 'datatable']);


// TODO нужен ли
//Route::get('/order/{order_id}/payouts/datatable', [PayoutController::class, 'datatable'])->middleware('auth.jwt');

//Route::post('/payouts/simulate', [PayoutController::class, 'simulate'])->middleware('auth.jwt')->middleware('is_identified');
//
//Route::post('/payouts/mass_pay', [PayoutController::class, 'massPay'])->middleware('auth.jwt')->middleware('is_identified');

//Route::post('/payouts/{payout_id}/repay', [PayoutController::class, 'repay'])
//    ->middleware('is_identified', 'auth.jwt', 'can:company.payouts.repay');

// TODO нужен ли
//Route::post('/order/{order_id}/payout', [PayoutController::class, 'new'])->middleware('auth.jwt')->middleware('is_identified');


//Route::get('/documents/datatable', [DocumentController::class, 'datatable'])
//    ->middleware('auth.jwt', 'can:company.documents.show');


Route::get('/documents/types', [DocumentController::class, 'types'])->middleware('auth.jwt');

//TODO эндпоинт для мобилки
Route::get('/documents', [DocumentController::class, 'documents'])
    ->middleware('auth.jwt');

//Route::get('/project/{project_id}/documents/datatable', [DocumentController::class, 'datatable'])->middleware('auth.jwt');


//Route::get('/document/{document_id}', [DocumentController::class, 'get'])
//    ->name('document.get')
//    ->middleware('can:company.documents.show');

Route::get('/document/show/{document_id}', [DocumentController::class, 'show']);

//Route::get('/documents/get', [DocumentController::class, 'getMany'])
//    ->name('documents.get-many')
//    ->middleware('auth.jwt', 'can:company.documents.show');

//Route::get('/documents/get/all', [DocumentController::class, 'getAll'])
//    ->name('documents.get-many')
//    ->middleware('auth.jwt', 'can:company.documents.show');

//Route::get('/documents/unrequested_signs_count', [DocumentController::class, 'unrequestedSignsCount'])
//    ->middleware('auth.jwt', 'can:company.documents.request_sign');

//Route::get('/documents/request_sign_documents', [DocumentController::class, 'requestSignDocuments'])
//    ->middleware('auth.jwt', 'can:company.documents.request_sign');

//Route::post('/documents/request_sign_documents_scope', [DocumentController::class, 'requestSignDocumentsInScope'])->middleware('auth.jwt');


//Route::post('/document/upload', [DocumentController::class, 'upload'])
//    ->middleware('is_identified', 'can:company.documents.create');

//Route::post('/project/{project_id}/document', [DocumentController::class, 'create'])
//    ->middleware('auth.jwt', 'is_identified', 'can:company.documents.create');




//Route::post('/npd/bind_to_partner', [UserController::class, 'bindToPartner'])->middleware('auth.jwt');
//
//Route::post('/npd/unbind_from_partner', [UserController::class, 'unbindFromPartner'])->middleware('auth.jwt');
//
//
//Route::post('/npd/cancel_bind_to_partner', [UserController::class, 'cancelBindToPartner'])->middleware('auth.jwt');



//Route::post('/my-company', [CompanyController::class, 'updateProfile'])->middleware('auth.jwt');


//Route::get('/company/by_inn/{inn}', [CompanyController::class, 'getDataByInn']);
Route::get('/regions', [RegionController::class, 'list']);
Route::any('/banks', [BankController::class, 'list']);
Route::get('/job_categories', [JobCategoryController::class, 'list']);


Route::get('/signme/check_identification', [UserController::class, 'checkSignMeIdentification'])->middleware('auth.jwt');

Route::any('/registration', [UserController::class, 'registration'])->middleware('auth.jwt');



//Route::post('task/{task_id}/invite_user/{user_id}', [TaskController::class, 'inviteUser'])
//    ->middleware('auth.jwt', 'can:company.tasks.contractor_assign');


Route::group(['prefix' => 'admin'], function () {
    Route::post('/users/fake', [UsersController::class, 'createFakeUser']);
    Route::get('/users', [UsersController::class, 'index']);
    Route::post('/users/{user_id}/identified_toggle', [UsersController::class, 'toggleIdentification']);
    Route::delete('/users/{user_id}', [UsersController::class, 'deleteUser']);

    Route::get('/docs', [\App\Http\Controllers\Admin\DocumentsController::class, 'index']);
    Route::post('/docs/sign/{doc_id}', [\App\Http\Controllers\Admin\DocumentsController::class, 'signDoc']);
});
