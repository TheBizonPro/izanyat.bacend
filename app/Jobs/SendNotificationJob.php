<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected string $subject;
    protected string $text;
    protected string $from;
    protected string $plainText;
    protected bool $isReaded;

    /**
     * @param User $user
     * @param string $subject
     * @param string $text
     * @param string $from
     * @param bool $isReaded
     */
    public function __construct(User $user, string $subject, string $text, string $plainText = '', string $from = 'Платформа',  bool $isReaded = false)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->text = $text;
        $this->from = $from;
        $this->plainText = $plainText;
        $this->isReaded = $isReaded;
    }


    public function handle()
    {
        $notification = new Notification;
        $notification->user_id = $this->user->id;
        $notification->is_readed = $this->isReaded;
        $notification->from = $this->from;
        $notification->subject = $this->subject;
        $notification->text = $this->text;
        $notification->plain_text = $this->plainText;
        $notification->save();
        Log::channel('debug')->debug('NOTIFICATION', $notification->toArray());
    }
}
