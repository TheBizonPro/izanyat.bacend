<?php

namespace App\Console\Commands;

use App\Helpers\PaymentSystemsHelper;
use App\Models\PaymentSystem;
use Illuminate\Console\Command;

class CreatePaymentSystemsCommand extends Command
{
    protected $signature = 'payment_systems:create';

    protected $description = 'Command description';

    public function handle()
    {
        dump('Начинаем создание платёжек');
        dump('Текущие платёжки: ', PaymentSystem::all()->toArray());
        PaymentSystemsHelper::createPaymentSystems();
        dump('Платёжки созданы');
    }
}
