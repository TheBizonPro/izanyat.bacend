<?php

namespace App\Console\Commands\FNS;

use App\Jobs\FNS\GetOfflineKeys;
use App\Models\User;
use Illuminate\Console\Command;

class GetAllOfflineKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-offline-key:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получить оффлайн ключи для всех подтвержденных пользователей';

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
        $userInns = User::query()
            ->select('inn')
            ->where('taxpayer_registred_as_npd', true)
            ->where('taxpayer_binded_to_platform', true)
            ->pluck('inn')
            ->toArray();

        if (!empty($userInns)) {
            GetOfflineKeys::dispatch($userInns);
        }
    }
}
