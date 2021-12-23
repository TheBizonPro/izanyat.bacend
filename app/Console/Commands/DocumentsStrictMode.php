<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DocumentsStrictMode extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:strict {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Переключение режима для обязательной проверки документов';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $value = $this->argument('value');

        $flag = false;

        if ($value == 'on') {
            $flag = true;
        }


        $users = User::all();
        $usersCounts = $users->count();

        $users->each(function ($user, $key) use ($usersCounts, $flag) {
            $number = $key + 1;
            dump("{$number}" . '/' . "{$usersCounts}");
            $user->must_have_task_documents = $flag;
            $user->save();
        });
        dump("DONE");
    }
}
