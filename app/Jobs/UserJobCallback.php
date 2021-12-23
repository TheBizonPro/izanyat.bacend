<?php


namespace App\Jobs;

use App\Models\User;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;

class UserJobCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    private SerializableClosure $callback;

    public function __construct(User $user, string $callback)
    {
        $this->user = $user;
        $this->callback = unserialize($callback);
    }

    public function handle()
    {
        $callback = $this->callback;
        $callback($this->user);
    }
}
