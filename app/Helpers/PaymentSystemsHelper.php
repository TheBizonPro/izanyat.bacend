<?php

namespace App\Helpers;

use App\Models\PaymentSystem;

class PaymentSystemsHelper
{
    public static function createPaymentSystems()
    {
        $systems = [
            'tinkoff' => [
                'code' => 'tinkoff',
                'display_name' => 'Тинькофф'
            ],
            'mobi' => [
                'code' => 'mobi',
                'display_name' => 'Моби Деньги'
            ]
        ];

        foreach ($systems as $system) {
            PaymentSystem::updateOrCreate([
                'code' => $systems['code']
            ], $system);
        }
    }
}
