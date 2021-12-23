<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mzcoding\SmsAero\SmsAero;

class SmsAeroSendMessageCommand extends Command
{
    protected $signature = 'smsaero:send';

    protected $description = 'Command description';

    public function handle()
    {
//        $receiverNumber = $this->terminal->input('Номер получателя: ');
//        $text = $this->terminal->input('Текст: ');

        try {
            $smsaero = new SmsAero();
            $a = $smsaero->send('79286320672', 'ааааа');
            dump($a);
        } catch (\Throwable $e) {
            dump($e->getMessage());
        }
    }
}
