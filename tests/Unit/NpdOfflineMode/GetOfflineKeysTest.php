<?php

namespace Tests\Unit\NpdOfflineMode;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetOfflineKeysTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ИНН пользователей
     *
     * @var array
     */
    private $userInns;

    public function setUp(): void
    {
        parent::setUp();

        $this->userInns = User::getInn();
    }

    /**
     * Проверка данных для запроса оффлайн ключей
     *
     * @group get-offline-keys-tests
     * @test get-keys-request
     * @return void
     */
    public function getKeysRequestTest()
    {
        $inns = $this->userInns;

        $this->assertIsArray($inns);
        $this->assertNotEmpty($inns);

        foreach ($inns as $inn) {
            $this->assertNotNull($inn);
            $this->assertIsString($inn);
        }
    }
}
