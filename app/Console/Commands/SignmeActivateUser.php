<?php

namespace App\Console\Commands;

use App\Services\LogsService;
use Illuminate\Console\Command;
use League\CLImate\CLImate;

use App\Models\User;
use App\Models\Company;

use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;

class SignmeActivateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signme:activate_user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Активация пользователя SignMe';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->terminal = new CLImate;
        $this->signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(LogsService $logsService)
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

        $this->terminal->out('Попытка активировать пользователя в signMe:');
        try {
            $result = $this->signMe->activateUser($signMeState->signme_id);
            dump($result);
            $logsService->signmeLog('Активирован пользователь', $user->id, $user->toArray());
        } catch (\Throwable $e) {
            $this->terminal->red('Ошибка: ' . $e->getMessage());
            $this->terminal->out('');
            exit;
        }

        if ($user->company == null) {
            $this->terminal->out('Конец работы скрипта');
            $this->terminal->out('');
            exit;
        }

        $this->terminal->green('Компания: ' . $user->company->name . ', ИНН ' . $user->company->inn);

        if ($user->company->signme_id == null) {
            $this->terminal->red('У компании не установлен signme request id');
            $this->terminal->out('');
            exit;
        }

        $this->terminal->out('Попытка активировать компанию в signMe:');
        try {
            $result = $this->signMe->activateCompany($user->company->signme_id);
            dump($result);
            $logsService->signmeLog('Активирована компании', $user->company->signer_user_id, $user->company->toArray());
        } catch (\Throwable $e) {
            $this->terminal->red('Ошибка: ' . $e->getMessage());
            $this->terminal->out('');
            exit;
        }

        $this->terminal->out('Конец работы скрипта');
        $this->terminal->out('');
        exit;
    }
}
