<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $phone;
    private $text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phone, $text)
    {
        $this->phone = $phone;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SmsService $smsService)
    {
        if (is_file(storage_path('app/public/sms-log.html')) == false) {
            file_put_contents(storage_path('app/public/sms-log.html'), '<HTML><meta charset="utf-8"><body>');
        }

        try {
            $fp = fopen(storage_path('app/public/sms-log.html'), 'a+');
            fwrite($fp, date('Y-m-d H:i:s') . "   +" . $this->phone . "    " . $this->text . "<br>");
        } catch(\Throwable $e) {
        }


        try {
            $a = $smsService->send($this->phone, $this->text);
            fwrite($fp, print_r($a, true) . '<br>');
        } catch(\Throwable $e) {
            fwrite($fp, "Ошибка отправки смс: " . $e->getMessage() . '<br>');
            throw new \Exception("Ошибка отправки смс: " . $e->getMessage());
        }

        try {
            fwrite($fp, '------<br>');
        } catch(\Throwable $e) {
        }

    }
}
