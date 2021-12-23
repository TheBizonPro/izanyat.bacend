<?php

namespace App\Http\Controllers\PaymentMethods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractorPaymentMethodsController extends Controller
{
    public function index(Request $request)
    {
        return [
             'payment_methods' => $request->user()->paymentMethods
        ];
    }
}
