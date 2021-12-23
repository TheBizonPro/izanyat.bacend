<?php

namespace App\Console\Commands\FNS;

use Illuminate\Console\Command;
use App\Models\User;

use App\Jobs\FNS\GetNotifications as FNSGetNotifications;

class LoadNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fns:load_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение новый уведомлений для самозанятых из ПП НПД';

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
     * @return int
     */
    public function handle()
    {
        dump("Получение новых уведомлений от ФНС");
        dump("Получаем пользователей самозанятых...");

        $users = User::where('is_selfemployed', '=', true)
            ->where('taxpayer_binded_to_platform', '=', true)
            ->get();

        dump("Полученно пользователей: " . $users->count());

        foreach ($users as $user) {
            dump("- Пользователь ИНН: " . $user->inn . " " . $user->name );
            dump("-- Получаем уведомления");

            try{
                FNSGetNotifications::dispatch($user);
                dump("-- Готово");
            } catch (\Throwable $e) {
                dump("-- Ошибка " . $e->getMessage());
            }
        }


    }
}
