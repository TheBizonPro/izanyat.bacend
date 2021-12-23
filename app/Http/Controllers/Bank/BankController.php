<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    public function list()
    {
        $banks = Bank::orderBy('name')->get();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Список банков загружен',
                'banks' => $banks
            ], 200, [], JSON_UNESCAPED_UNICODE||JSON_UNESCAPED_SLASHES);
    }
}
