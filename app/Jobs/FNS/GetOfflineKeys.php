<?php

namespace App\Jobs\FNS;

use App\Services\Fns\OfflineKeysService;
use App\Services\Fns\FNSService;
use App\Services\Telegram\TelegramAdminBotClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

/**
 * Получает оффлайн ключи по ИНН пользователя
 *
 * Class GetOfflineKeys
 * @package App\Jobs\FNS
 */
class GetOfflineKeys implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $userInns;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $userInns)
    {
        $this->userInns = $userInns;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(FNSService $fnsService)
    {
        if (!isset($this->userInns) || !is_array($this->userInns) || empty($this->userInns))
            return;

        $response = $fnsService->offlineKeys($this->userInns)->json();

        if (Arr::exists($response, 'Code') and Arr::exists($response, 'Message')) {
            $Message = Arr::get($response, 'Message');
            throw new Exception($Message);
        }

        if (!Arr::exists($response, 'Keys')) {
            throw new Exception('Ошибка получения оффлайн ключей. API ФНС вернул неопределенный ответ. ' . json_encode($response));
        }

        $offlineKeysService = new OfflineKeysService();
        $offlineKeysService->setUsersKey($response);
    }
}
