<?php

namespace App\Http\Controllers\PaymentMethods;

use App\Http\Controllers\Controller;
use App\Models\Definitions\PaymentsSystemsNames;

class CompanyPaymentMethodsController extends Controller
{
    public function index()
    {
        return [
          'payment_methods' => [
              PaymentsSystemsNames::TINKOFF
          ]
        ];
    }
}
