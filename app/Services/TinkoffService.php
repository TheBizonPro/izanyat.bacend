<?php

namespace App\Services;

use App\Helpers\ArrayHelper;
use App\Jobs\SendNotificationJob;
use App\Models\TinkoffContractorCard;
use App\Models\User;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class TinkoffService
{
    protected User $user;
    protected LogsService $logsService;

    /**
     * @param User $user
     * @param LogsService $logsService
     */
    public function __construct(User $user, LogsService $logsService)
    {
        $this->user = $user;
        $this->logsService = $logsService;
    }


    public function handleBindCardReject(array $rejectCardData)
    {
        $this->logsService->tinkoffLog('Неудачная поп ит ка привязать карту', $this->user->id, $rejectCardData);
        SendNotificationJob::dispatch(
            $this->user,
            'Ошибка привязки банковской карты',
            'Привязка карты прошла неуспешно. Код ошибки: ' . $rejectCardData['ErrorCode'],
            'Привязка карты прошла неуспешно. Код ошибки: ' . $rejectCardData['ErrorCode']
        );
    }

    public function handleBindCardSuccess(array $successCardData, User $user)
    {
        $this->logsService->tinkoffLog('Успешная поп ит ка привязки карты, создаем карту у нас', $this->user->id, $successCardData);

        TinkoffContractorCard::createOne($user, [
            'tinkoff_contractor_account_id' => $user->id,
            'card_id' => $successCardData['CardId'],
            'card_number' => $successCardData['Pan'],
            'card_expires' => $successCardData['ExpDate'],
        ]);

        SendNotificationJob::dispatch(
            $this->user,
            'Банковская карта привязана успешно',
            "Банковская карта была успешно привязана",
            "Банковская карта была успешно привязана",
        );
    }

}
