<?php

namespace App\Console\Commands;

use App\Jobs\FNS\IncomeFiscalization;
use App\Jobs\FNS\OfflineFiscal;
use App\Models\Payout;
use Illuminate\Console\Command;

class TestOfflineMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-offline-mode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование оффлайн мода пакета ФНС';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $payout = Payout::create([
            'project_id'      => 1,
            'task_id'         => 1,
            'user_id'         => 59,
            'job_category_id' => 5,
            'sum'             => 6666,
            'status'          => 'process'
        ]);

        $IncomeFiscalization = new IncomeFiscalization($payout);

        try {
            $IncomeFiscalization->handle();
        } catch (\Throwable $e) {
            $payout->status = 'error';
            $payout->description = 'Ошибка фискализации входящего дохода: ' . $e->getMessage();
            $payout->save();

            OfflineFiscal::dispatch($payout);

            dump($e->getMessage(), $e->getCode());
        }
    }
}
