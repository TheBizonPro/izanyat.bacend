<?php

namespace Tests\Unit\NpdOfflineMode;

use App\Models\User;
use App\Services\Fns\OfflineKeysService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OfflineKeysServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Пример ответа api НПД
     *
     * @var array
     */
    private $npdRequest;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::first();

        $this->npdRequest = [
            'Keys' => [
                'Inn' => $user->inn,
                'KeyRecord' => [
                    'SequenceNumber' => 123,
                    'ExpireTime'     => Carbon::now()->addDays(7)->format('d-m-y H:i'),
                    'Base64Key'      => 'asQdyHfLghMTXOUQDlI6lP74/fuRhv8OPBnUa8+FYZg='
                ]
            ]
        ];
    }

    /**
     * Проверка данных для запроса оффлайн ключей
     *
     * @group set-users-keys-tests
     * @test set-users-keys
     * @return void
     * @throws Exception
     */
    public function setUsersKeys()
    {
        $request = $this->npdRequest;
        $offlineKeysService = new OfflineKeysService();

        $result = $offlineKeysService->setUsersKey($request);
        $this->assertTrue($result);
    }
}
