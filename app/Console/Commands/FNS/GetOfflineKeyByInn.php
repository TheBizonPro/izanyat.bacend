<?php

namespace App\Console\Commands\FNS;

use App\Jobs\FNS\GetOfflineKeys;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;

class GetOfflineKeyByInn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-offline-key:inn {inn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получить оффлайн ключ по указанному ИНН';

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
     * @throws Exception
     */
    public function handle()
    {
        $inn = $this->argument('inn');
        $users = User::query()->where('inn', $inn)->get();

        print "INN: '" . $inn . "'\n";
        print "Пользователей с таким ИНН: " . $users->count() . "\n";

        $userInns = $users->pluck('inn')->toArray();

        if (empty($userInns)) {
            throw new Exception('Пользователь не найден');
        }

        print "Взято в работу\n";

        GetOfflineKeys::dispatch($userInns);
    }
}
