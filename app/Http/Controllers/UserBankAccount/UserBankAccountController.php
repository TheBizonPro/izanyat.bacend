<?php

namespace App\Http\Controllers\UserBankAccount;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request\StoreBankAccountRequest;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;

class UserBankAccountController extends Controller
{
    public function store(StoreBankAccountRequest $request)
    {
        $bankAccountData = $request->validated();
        $bankAccountData['user_id'] = $request->user()->id;

        $bankAccount = UserBankAccount::updateOrCreate([
            'user_id' => $bankAccountData['user_id']
        ], $bankAccountData);

        return [
            'bank_account' => $bankAccount->toArray()
        ];
    }

    public function get(Request $request): array
    {
        return [
            'bank_account' => $request->user()->bankAccount
        ];
    }
}
