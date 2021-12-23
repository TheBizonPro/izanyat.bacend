<?php

namespace App\Console\Commands;

use App\Jobs\FNS\GetNotifications;
use App\Models\Payout;
use App\Models\User;
use App\Services\MobiPaymentsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Carbon\Carbon;
use VoltSoft\FnsSmz\FnsSmzApi;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzClient as Client;

class SendSomeSoapShitCommand extends Command
{
    protected $signature = 'test:soap';

    protected $description = 'Command description';

    /**
     * @throws \SoapFault
     * @throws \Exception
     */
    public function handle()
    {
        $payout = Payout::firstOrFail();

        for($i = 0; $i < 15; $i++) {
            $newPayout = new Payout();
            $newPayoutData = $payout->toArray();
            unset($newPayoutData['id']);
            $newPayout->fill($newPayoutData);
            $newPayout->save();
        }
    }
}
