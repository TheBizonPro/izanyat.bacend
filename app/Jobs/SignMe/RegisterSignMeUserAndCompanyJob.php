<?php

namespace App\Jobs\SignMe;

use App\Jobs\SendNotificationJob;
use App\Models\SignMeUserState;
use App\Models\User;
use App\Services\SignMeService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegisterSignMeUserAndCompanyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function handle(SignMeService $signMeService)
    {
        $user = $this->user;
        $company = $this->user->company;

        if (!isset($company->signme_id) and $user->id !== $company->signer_user_id) {
            SendNotificationJob::dispatch(
                $user,
                'Регистрация в SignMe',
                'Ваша компания не зарегистрирована в SignMe, обратитесь к регистратору компании',
                'Ваша компания не зарегистрирована в SignMe, обратитесь к регистратору компании',
            );
            throw new \Exception('Компания не зарегистрирована в SignMe. Обратитесь к регистратору компании');
        }

        $signMeState = $user->signMeState ?? SignMeUserState::create(['user_id' => $this->user->id]);

        $signMeState->update([
            'status' => 'request_in_progress'
        ]);

        $signMeService->setUser($this->user);

        try {
            // если компания не зарегистрирована
            if (!isset($company->signme_id) and $user->id === $company->signer_user_id) {
                $registrationResult = $signMeService->registerUserAndCompany();
                return;
            }

            // если компания зарегана, а пользователь нет
            else if (!isset($signMeState->signme_id)) {
                $registrationResult = $signMeService->registerUser();
            }
        } catch (\Throwable $e) {
        }
    }
}
