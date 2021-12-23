<?php

namespace App\Jobs\SignMe;

use App\Services\LogsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Telegram\TelegramAdminBotClient;

use PackFactory\SignMe\DTO\CompanyDTO;
use PackFactory\SignMe\DTO\QrDTO;
use PackFactory\SignMe\DTO\RegistrationRequestDTO;
use PackFactory\SignMe\DTO\UserDTO;
use PackFactory\SignMe\DTO\UserInfoDTO;
use PackFactory\SignMe\Enums\Region;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;
use Illuminate\Support\Facades\Log;




class CheckIdentification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $signMe;
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $skipRemove = false)
    {
        $this->user = $user;
        $this->signMe = new SignMe(config('signme.key'), config('signme.sandbox'), null, config('signme.logging.start'), storage_path('logs/signme_' . uniqid() . '.log'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogsService $logsService)
    {
        $user = $this->user;

        /**
         * Проверяем, существуют ли заявки на создание юзера
         */
        $UserDTO = null;
        $user_exists = $this->signMe->precheck([
            'inn' => $user->forSignMe('inn'),
            // 'phone' => $user->forSignMe('phone'),
            // 'snils' => $user->forSignMe('snils'),
            // 'email' => $user->forSignMe('email'),
        ]);
        /**
         * Если заявки на пользователя уже существуют - удалим их
         */
        if (count($user_exists) > 0 ) {
            //dump('Заявка с таким пользователем уже существует! Удаляем ее!');
            $requests = [];
            foreach ($user_exists as $CheckDTO) {
                if ($CheckDTO->approved == true) {
                    $logsService->signmeLog('Пользователь подтвержден', $user->id);
                    return true;
                }
            }
        }

        return false;

    }
}
