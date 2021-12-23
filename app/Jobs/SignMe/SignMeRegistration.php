<?php

namespace App\Jobs\SignMe;

use App\Jobs\Notification\SignmeNotifications;
use App\Models\SignMeUserState;
use App\Services\LogsService;
use App\Services\SignMeService;
use App\Services\Telegram\TelegramAdminBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface;
use Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Document;


use PackFactory\SignMe\DTO\CompanyDTO;
use PackFactory\SignMe\DTO\QrDTO;
use PackFactory\SignMe\DTO\RegistrationRequestDTO;
use PackFactory\SignMe\DTO\UserDTO;
use PackFactory\SignMe\DTO\UserInfoDTO;
use PackFactory\SignMe\Enums\Region;
use PackFactory\SignMe\Exceptions\SignMeResponseException;
use PackFactory\SignMe\SignMe;



class SignMeRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogsService $logsService, SignMeService $signMeService)
    {
        $user = $this->user;
        $signMeState = $user->signMeState ?? SignMeUserState::create(['user_id' => $this->user->id]);

        $signMeState->update([
            'status' => 'request_in_progress'
        ]);

        try {
            $signMeService->setUser($user)->registerUser();
        } catch (ClientExceptionInterface | \Exception $e) {
            $logsService->signmeLog('Ошибка при регистрации в SignMe', $this->user->id, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
