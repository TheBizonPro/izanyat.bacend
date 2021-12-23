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

class UpdateReceiptsFromFNS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $afterDispatchHandler;
    protected int $userId;

    public function __construct(callable $afterDispatchHandler, int $userId)
    {
        $this->afterDispatchHandler = $afterDispatchHandler;
        $this->userId = $userId;
    }

    public function handle(FNSService $fnsService)
    {
        $user = User::query()->findOrFail($this->userId);

        $now = Carbon::now();

        dump('Получаем данные о доходах пользователя с ИНН ' . $user->inn);
        dump($now->subDays(40)->isoFormat('YYYY-MM-DDThh:mm:ss'));

        $response = $fnsService->receiptIncome($user->inn);

        $receipts = $response['Receipts'];

        dump('Получено чеков: ' . count($receipts));

        $canceledReceipts = array_filter($receipts, function (array $receipt) {
            return isset($receipt['CancelationTime']);
        });

        dump('Отмененных чеков: ' . count($canceledReceipts));

        foreach ($canceledReceipts as $canceledReceipt) {
            $id = $canceledReceipt['ReceiptId'];

            $payout = Payout::whereReceiptId('ReceiptId')->first();

            if (!$payout) continue;

            $payout->setStatus();
        }
    }
}
