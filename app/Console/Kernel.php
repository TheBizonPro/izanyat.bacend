<?php

namespace App\Console;

use App\Console\Commands\DeleteAllSignmeEntitiesCommand;
use App\Console\Commands\deployment\DeployInitCommand;
use App\Console\Commands\deployment\DeployUpdateCommand;
use App\Console\Commands\FNS\GetOfflineKeysCommand;
use App\Console\Commands\GenerateDatabaseDumpCommand;
use App\Console\Commands\SendSomeShitCommand;
use App\Console\Commands\SendSomeSoapShitCommand;
use App\Console\Commands\SetPermissionsCommand;
use App\Console\Commands\Signme\ActivateCompanyCommand;
use App\Console\Commands\Signme\CheckByInnCommand;
use App\Console\Commands\Signme\DeleteSignmeEntityCommand;
use App\Console\Commands\SmsAeroSendMessageCommand;
use App\Console\Commands\TestSchedulerCommand;
use App\Console\Commands\UpdateUsersReceiptsStatusesFromFNS;
use App\Jobs\FNS\UpdateUsersOfflineKeysJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        UpdateUsersReceiptsStatusesFromFNS::class,
        CheckByInnCommand::class,
        ActivateCompanyCommand::class,
        DeleteSignmeEntityCommand::class,
        DeleteAllSignmeEntitiesCommand::class,
        SmsAeroSendMessageCommand::class,
        GenerateDatabaseDumpCommand::class,
        GetOfflineKeysCommand::class,
        SendSomeSoapShitCommand::class,
        SendSomeShitCommand::class,
        SetPermissionsCommand::class,
        TestSchedulerCommand::class,
        DeployInitCommand::class,
        DeployUpdateCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('signme:check_documents')->everyMinute();
        $schedule->command('fns:check_bound_taxpayers')->everyTenMinutes();
        $schedule->command('database:dump')->hourly();

        $schedule->command('fns:get_unbound_taxpayers')->everyTenMinutes();

        $schedule->command('fns:load_notifications')->everyTenMinutes();
        //        $schedule->command('scheduler:test')->everyMinute();

        $schedule->job(new UpdateUsersOfflineKeysJob)->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
