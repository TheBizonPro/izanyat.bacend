<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UserRegistrationExeption;
use App\Http\Controllers\Controller;
use App\Jobs\MakeFakeUserJob;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function createFakeUser(Request $request)
    {
        try {
            MakeFakeUserJob::dispatchNow($request->userType);
        } catch (UserRegistrationExeption $e) {
            return [
                'errors' => $e->getMessage()
            ];
        }
        return response()->json([
            'message' => 'Начали создание фейкового юзера'
        ]);
    }

    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->company?->forceDelete();
        $user?->forceDelete();

        return [
            'message' => 'чел'
        ];
    }

    public function toggleIdentification(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->update([
            'is_identified' => $user->is_identified === 1 ? 0 : 1
        ]);

        return [
            'user' => $user
        ];
    }

    public function index()
    {
        return [
            'users' => User::all()->toArray()
        ];
    }
}
