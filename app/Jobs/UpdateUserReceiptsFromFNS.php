<?php

namespace App\Jobs;

use App\Models\Payout;
use App\Models\User;
use App\Services\Fns\FNSApiClientHelper;
use App\Services\Fns\FNSService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateUserReceiptsFromFNS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(FNSService $fnsService)
    {
        $response = $fnsService->receiptIncome($this->user->inn);

        $receipts = $response['Receipts'] ?? throw new \Exception('Налоговая не вернула чеки');

        $canceledReceipts = array_filter($receipts, function (array $receipt) {
            return isset($receipt['CancelationTime']);
        });

        foreach ($canceledReceipts as $canceledReceipt) {
            $id = $canceledReceipt['ReceiptId'];

            $payout = Payout::whereReceiptId($id)->first();

            if (!$payout) continue;
            if ($payout->status === 'canceled') continue;

            $payout->setStatusCanceled();

            $project = $payout->task->project;

            $project->users->each(function ($user) use ($payout) {
                SendNotificationJob::dispatch(
                    $user,
                    'Исполнитель аннулировал чек',
                    "Чек по задаче <a href='/contractor/task/{$payout->task->id}'>$payout->task->name</a> аннулирован",
                    $payout->user->full_name
                );
            });

            SendNotificationJob::dispatch(
                $payout->project->company->signerUser,
                'Исполнитель аннулировал чек',
                "Чек по задаче <a href='/contractor/task/{$payout->task->id}'>$payout->task->name</a> аннулирован",
                $payout->user->full_name
            );
        }
    }
}
