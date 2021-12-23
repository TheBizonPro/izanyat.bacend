<?php

namespace App\Jobs\FNS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;
use App\Models\Notification;
use App\Services\Fns\FNSService;

class GetNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FNSService $fnsService)
    {
       $answer = $fnsService->getNotifications($this->user->inn);
        /**
         * В случае ошибки - выкидываем исключение
         */
        if (Arr::exists($answer, 'Code') and Arr::exists($answer, 'Message')) {
            $Message = Arr::get($answer, 'Message');
            throw new \Exception($Message);
        }

        $fnsNotifications = Arr::get($answer, 'notificationsResponse.notif');
        if ($fnsNotifications != null) {
            foreach (Arr::get($answer, 'notificationsResponse.notif') as $fnsNotification) {
      			$fns_id = Arr::get($fnsNotification, 'id');
      			$notificationExists = Notification::where('fns_id', '=', $fns_id)->exists();
      			if ($notificationExists == false) {
	                $notification = new Notification;
	                $notification->fns_id = $fns_id;
	                $notification->user_id = $this->user->id;
	                $notification->is_readed = false;
	                $notification->from = 'ФНС ПП НПД';
	                $notification->subject = Arr::get($fnsNotification, 'title');
	                $notification->text = Arr::get($fnsNotification, 'message');
	                $notification->plain_text = Arr::get($fnsNotification, 'message');
                    $created_at_dt = new Carbon(Arr::get($fnsNotification, 'createdAt'), 'UTC');
	               // $notification->created_at = $created_at_dt->shiftTimezone(new \DateTimeZone('Europe/Moscow'))->format('Y-m-d H:i:s');
	                $notification->save();
	                try {
                        $fnsService->notificationDelivered($this->user->inn, $notification->fns_id);
	                } catch (\Throwable $e) {}
      			}
            }
        } else {
            throw new \Exception("Ошибка получения уведомлений самозанятого из ПП НПД. API ФНС вернул неопределенный ответ");
        }
    }
}
