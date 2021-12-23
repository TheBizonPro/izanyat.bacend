<?php

namespace App\Services;

use App\Jobs\Documents\CreateAct;
use App\Jobs\Documents\CreateWorkOrder;
use App\Jobs\SendNotificationJob;
use App\Models\Company;
use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Log;
use PhpParser\Comment\Doc;

class TasksService
{
    #[ArrayShape(['result' => "bool", 'reasons' => "array"])]
    function taskCanBePayed(Task $task): array
    {
        $actSigned = $task->act !== null
            &&
            $task->act->company_sig !== null
            &&
            $task->act->user_sig !== null;
        if ($task->user->must_have_task_documents === 0)
            $actSigned = true;

        $userCanReceiveMoney = $task->user->bankAccount?->hasBankCard();

        $companyHasPaymentAccount = $task->company()->bankAccount?->mobi_connected;

        $isUserNPD = $task->user->taxpayer_registred_as_npd;

        //TODO ПЕРЕПИСАТЬ ПЕРЕД ПРОДАКШНОМ
        //        $companyHanEnoughMoney = $task->company()->balance >= $task->sum;
        $companyHanEnoughMoney = true;

        $reasons = [];

        if (!$actSigned)
            $reasons[] = 'Не подписан акт';

        if (!$isUserNPD) {
            $reasons[] = 'Исполнитель не стоит на учете в качестве плательщика НПД';

            //TODO переписать это под адекватный коллбек или возвращать коды, кароче не слать уведомление в функции-чекере
            SendNotificationJob::dispatch(
                $task->user,
                'Невозможно перевести деньги',
                "Задача <a href='/contractor/task/{$task->id}'>$task->name</a> не может быть оплачена, так как вы не привязали \"Мой Налог\" к платфроме ЯЗанят"
            );
        }

        if (!$userCanReceiveMoney) {
            $reasons[] = 'Пользователь не указал реквизиты';
            SendNotificationJob::dispatch(
                $task->user,
                'Невозможно перевести деньги',
                "Задача <a href='/contractor/task/{$task->id}'>$task->name</a> не может быть оплачена, так как вы не указали реквизиты"
            );
        }

        if (!$companyHanEnoughMoney)
            $reasons = 'Недостаточно средств';

        if (!$companyHasPaymentAccount) {
            $reasons[] = 'Для проведения выплаты необходимо подключить аккаунт МОБИ Деньги';

            $project = Project::find($task->project_id);
            $project->users->each(function ($user) use ($task) {
                SendNotificationJob::dispatch(
                    $user,
                    'Невозможно перевести деньги',
                    "Задача <a href='/contractor/task/{$task->id}'>$task->name</a> не может быть оплачена, так как получатель не указал реквизиты"
                );
            });

            SendNotificationJob::dispatch(
                $task->company()->signerUser,
                'Невозможно перевести деньги',
                "Задача <a href='/contractor/task/{$task->id}'>$task->name</a> не может быть оплачена, так как получатель не указал реквизиты"
            );
        }

        return [
            'result' => $userCanReceiveMoney && $actSigned && $companyHasPaymentAccount && $isUserNPD,
            'reasons' => $reasons
        ];
    }

    function contractorConfiguredPaymentMethod(Task $task) {
        $contractor = $task->user;
        $paymentSystem = $task->paymentSystem;

        $contractor->getPaymentSystemAccount($paymentSystem);

        return true;
    }

    function companyConfiguredPaymentMethod(Task $task) {
        return true;
    }

    function isTaskHasDocumentsToBeDone(Task $task): bool
    {
        if ($task->user->must_have_task_documents === 0) return true;

        $order = $task->order;
        $agreement = Document::whereUserId($task->user_id)
            ->whereProjectId($task->project_id)
            ->whereCompanyId($task->company()->id)
            ->whereType('contract')
            ->first();

        if (!$agreement)
            return false;

        return $this->isSigned($order) && $this->isSigned($agreement);
    }

    function isTaskCanBeDone(Task $task): bool
    {
        if ($task->user->must_have_task_documents === 0) return true;

        $userCanDoTasks = $this->isUserCanDoTasks($task->user, $task->project->company);
        $taskHasDocumentsToBeDone = $this->isTaskHasDocumentsToBeDone($task);

        $order = $task->order;

        // задача может быть подписана, только если есть договор, заказ и они подписаны
        return
            $order->company_sign_requested && $order->user_sign_requested
            && $taskHasDocumentsToBeDone
            && $userCanDoTasks;
    }

    public function isUserCanDoTasks(User $user, Company $company): bool
    {
        if ($user->must_have_task_documents === 0) return true;

        $agreement = Document::whereUserId($user->id)
            ->whereCompanyId($company->id)
            ->whereType('contract')
            ->first();

        if ($agreement === null) return false;


        return
            $agreement->company_sig !== null
            &&
            $agreement->user_sig !== null;
    }

    function assign(Task $task, User $user): Task
    {
        DB::beginTransaction();
        $task->status = 'work';
        $task->user_id = $user->id;
        $task->save();

        CreateWorkOrder::dispatchSync($task);

        DB::commit();

        return $task;
    }

    /**
     * @throws \Exception
     */
    function taskDone(Task $task)
    {
        if (!$this->isTaskCanBeDone($task)) {
            throw new \Exception('Task cannot be done');
        }

        $task->status = 'done';
        $task->save();

        // CreateAct::dispatch($task);
    }

    function clientTaskDone(Task $task)
    {
        Log::channel('debug')->debug('DONE!');
        $task->status = 'await_payment_request';
        $task->save();
        Log::channel('debug')->debug('1234');
        CreateAct::dispatch($task);
        SendNotificationJob::dispatch($task->user, 'Необходимо подписать документы', "Заказчик принял задачу #$task->id «{$task->name}». Для получения вознаграждения необходимо подписать акт");

        $project = Project::find($task->project_id);
        $project->users->each(function ($user) use ($task) {
            SendNotificationJob::dispatch($user,  'Необходимо подписать документы', "Для отправки вознаграждения  за задачу #$task->id «{$task->name}» необходимо подписать акт");
        });

        SendNotificationJob::dispatch($task->project->company->signerUser,  'Необходимо подписать документы', "Для отправки вознаграждения  за задачу #$task->id «{$task->name}» необходимо подписать акт");
    }

    private function isSigned(Document $document): bool
    {
        return $document->company_sig !== null && $document->user_sig !== null;
    }
}
