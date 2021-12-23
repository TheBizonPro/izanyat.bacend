<?php

namespace App\Jobs;

use App\Jobs\FNS\BindTaxpayer as FNSBindTaxpayer;
use App\Jobs\FNS\UpdateTaxpayerData as FNSUpdateTaxpayerData;
use App\Jobs\Notification\FNSBindingError as FNSBindingErrorNotification;
use App\Jobs\Notification\FNSBindingNeedAction as FNSBindingNeedActionNotification;
use App\Jobs\Notification\FNSBindingOk as FNSBindingOkNotification;
use App\Models\User;
use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BindTaxpayerToPlatformJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function handle(LogsService $logsService)
    {
        try {
            $bindStatus = FNSBindTaxpayer::dispatchNow($this->user);
        } catch(\Throwable $e){
            $bindStatus = 'error';
        }

        if ($bindStatus == 'bind_requested') {
            FNSBindingNeedActionNotification::dispatchNow($this->user);
        } else if ($bindStatus == 'already_bound') {
            FNSBindingOkNotification::dispatchNow($this->user);
        } else if ($bindStatus == 'error') {
            FNSBindingErrorNotification::dispatchNow($this->user);
        }

        FNSUpdateTaxpayerData::dispatch($this->user);
    }
}
