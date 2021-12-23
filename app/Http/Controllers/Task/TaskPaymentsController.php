<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAssignTaskPaymentMethodRequest;
use App\Http\Requests\ContractorAssignTaskPaymentMethodRequest;
use App\Models\Definitions\PaymentsSystemsNames;
use App\Models\Task;
use App\Services\TasksService;

class TaskPaymentsController extends Controller
{
    protected TasksService $tasksService;

    /**
     * @param TasksService $tasksService
     */
    public function __construct(TasksService $tasksService)
    {
        $this->tasksService = $tasksService;
    }


    public function assignContractorPaymentMethod(ContractorAssignTaskPaymentMethodRequest $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $task->update([
            'contractor_payment_method_id' => $request['payment_method_id']
        ]);

        return [
            'task' => $task
        ];
    }

    public function assignCompanyPaymentMethod(CompanyAssignTaskPaymentMethodRequest $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        // сейчас захардкожено т.к есть только тинек
        $task->update([
            'company_payment_type' => PaymentsSystemsNames::TINKOFF
        ]);

        return [
            'task' => $task
        ];
    }
}
