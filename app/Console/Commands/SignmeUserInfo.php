<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SignMeService;
use Illuminate\Console\Command;
use League\CLImate\CLImate;
use PackFactory\SignMe\SignMe;

class SignmeUserInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signme:userinfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Активация пользователя SignMe';

    public function __construct()
    {
        parent::__construct();
        $this->terminal = new CLImate;
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function handle(SignMeService $signMeService)
    {
        $this->terminal->out('');
        $this->terminal->out('');
        $input = $this->terminal->input('Введите ИНН пользователя, которого необходимо активировать: ');
        $inn = $input->prompt();

        $user = User::where('inn', '=', trim($inn))->firstOrFail();
        $signMeState = $user->signMeState;
        if (!isset($signMeState)) {
            $this->terminal->red('Пользователь с ИНН не найден');
            $this->terminal->out('');
            exit;
        }

        $this->terminal->green('Пользователь: ' . $user->name . ', ИНН ' . $inn);

        if ($signMeState->signme_id == null) {
            $this->terminal->red('У пользователя не установлен signme request id');
            $this->terminal->out('');
            exit;
        }
        $signMeService->setUser($user);
        try {
            $res = $signMeService->userInfo();
            dump($res);
            $response = $signMeService->certInfo();
            dump($response);
        } catch (\Throwable $e) {
            dd($e->getTrace());
            $this->terminal->red('Ошибка: ' . $e->getMessage());
            $this->terminal->out('');
            exit;
        }
    }
}
