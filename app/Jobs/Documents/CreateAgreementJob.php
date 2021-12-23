<?php

namespace App\Jobs\Documents;

use App\Models\Payout;
use App\Services\Documents\DocumentsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateAgreementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Payout $payout;

    /**
     * @param Payout $payout
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }


    public function handle(DocumentsService $documentsService)
    {
        try {
//            $documentsService->createAgreement($this->payout);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
