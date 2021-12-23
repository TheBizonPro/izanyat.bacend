<?php

namespace App\Jobs\FNS;

use App\Services\Fns\FNSService;
use App\Services\LogsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Arr;
use App\Models\Payout;
use Illuminate\Support\Facades\Log;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class IncomeFiscalization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $errorCode;
    private $payout;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(FNSService $fnsService, LogsService $logsService)
    {

        /**
         * Формируем запрос на фискализацию входящих денег
         */
        $Inn = $this->payout->user->inn;
        $RequestTime = $this->payout->created_at->toAtomString();
        $OperationTime = $this->payout->created_at->toAtomString();
        $IncomeType = 'FROM_LEGAL_ENTITY';
        $CustomerInn = $this->payout->project->company->inn;
        $CustomerOrganization = $this->payout->project->company->name;
        $Service = [];
        $Service['Amount'] = $this->payout->sum;
        $Service['Name'] = $this->payout->task->name;
        $Service['Quantity'] = 1;
        $Services = [$Service];
        $TotalAmount = $this->payout->sum;
        $ReceiptId = $this->payout->receipt_id ?: null;
        $Link = $this->payout->receipt_url ?: null;
        $IncomeHashCode = $this->payout->receipt_uuid ?: null;

        /**
         * Отправляем запрос
         */
        $answer = $fnsService->incomeFiscalization(
            $this->payout->user->inn,
            $this->payout->receipt_id ?: null,
            $this->payout->created_at->toAtomString(),
            $this->payout->created_at->toAtomString(),
            $this->payout->project->company->inn,
            $this->payout->project->company->name,
            $this->payout->sum,
            $this->payout->task->name,
            $this->payout->sum,
            $this->payout->receipt_url ?: null,
            $this->payout->receipt_uuid ?: null
        );
        $answer = $this->prepareResponseArray($answer->json());

        $logsService->userLog('Получен ответ от ФНС по фискализации дохода', $this->payout->user_id, $answer);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Code = Arr::get($answer, 'Code');
            $this->errorCode = $Code;
            $Message = Arr::get($answer, 'Message');

            throw new Exception($Message . ' (код ошибки: ' . $Code . ').',);
        }

        if (Arr::exists($answer, 'ReceiptId') and Arr::exists($answer, 'Link')) {
            $this->payout->receipt_id = Arr::get($answer, 'ReceiptId');
            $this->payout->receipt_url = Arr::get($answer, 'Link');
            $this->payout->save();
            return true;
        }

        Log::channel('debug')->debug('Ошибка фискализации дохода: ', $answer);

        throw new Exception("Ошибка фискализации входящего платежа в ПП НПД. API ФНС вернул неопределенный ответ.");
    }

    private function prepareResponseArray(array $responseArray): array
    {
        foreach ($responseArray as $index => $item) {
            if (!str_starts_with($index, 'ns2:'))
                continue;

            $keyWithoutNamespace = str_replace('ns2:', '', $index);
            $responseArray[$keyWithoutNamespace] = $item;
            unset($responseArray[$index]);
        }

        return $responseArray;
    }
}
