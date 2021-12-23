<?php

namespace App\Mail;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MobiPaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    private Payout $payout;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::channel('debug')->debug('MESSAGE');
        return $this->subject("Оплата задачи #{$this->payout->task->id} '{$this->payout->task->name}'")->view('emails.mobiSuccess',['payout'=>$this->payout]);
    }
}
