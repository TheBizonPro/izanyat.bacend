<?php

namespace App\Console\Commands\FNS;

use App\Exceptions\NpdPlatformRegistrationException;
use App\Models\PlatformInfo;
use App\Services\Fns\FNSService;
use Exception;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use VoltSoft\FnsSmz\FnsSmzClient;
use VoltSoft\FnsSmz\FnsSmzApi;

class PlatformRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fns:platform_registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Регистрация платформы в ПП НПД';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws NpdPlatformRegistrationException
     * @throws Exception
     */
    public function handle(FNSService $fnsService)
    {
        $response = $fnsService->platformRegistration();

        dump($response->body());

        if (Arr::exists($response, 'Code') and Arr::exists($response, 'Message')) {
            $message = Arr::get($response, 'Message');
            throw new NpdPlatformRegistrationException($message);
        }

        if (!Arr::exists($response, 'PartnerID') || !Arr::exists($response, 'RegistrationDate')) {
            throw new NpdPlatformRegistrationException(
                'Ошибка регистрации платформы. API ФНС вернул неопределенный ответ.'
            );
        }

        PlatformInfo::updatePartnerInfo($response['PartnerID'], $response['RegistrationDate']);
    }
}
