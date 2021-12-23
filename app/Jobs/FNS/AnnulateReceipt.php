<?php

namespace App\Jobs\FNS;

use App\Services\Fns\FNSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Arr;
use App\Models\User;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class AnnulateReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $Inn;
    private $ReceiptId;
    private $ReasonCode;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($Inn,  $ReceiptId, $ReasonCode)
    {
        $this->Inn = $Inn;
        $this->ReceiptId = $ReceiptId;
        $this->ReasonCode = $ReasonCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FNSService $fnsService)
    {

        $answer = $fnsService->annulateReceipt($this->Inn, $this->ReceiptId, $this->ReasonCode);

        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        //ddh( $answer);

        return true;
    }
}
