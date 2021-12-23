<?php

namespace App\Providers;

use App\Jobs\JobsEventHandlers;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use TheSeer\Tokenizer\Exception;

class JobsServiceProvider extends ServiceProvider
{
    protected array $jobsProcessedHandlers;
    /**
     * Register services.
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $jobsProcessedHandlers = JobsEventHandlers::getBeforeDispatchEventHandlers();

        Event::listen(function (JobProcessed $job) use ($jobsProcessedHandlers) {
            $jobName = $job->job->resolveName();

            if (isset($jobsProcessedHandlers[$jobName]) && is_callable($jobsProcessedHandlers[$jobName])) {
                try {
                    ($jobsProcessedHandlers[$jobName])($job);
                } catch (Exception $exception) {}
            }
        });
    }
}
