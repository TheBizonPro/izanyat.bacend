<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function isPasswordCorrect(string $password, string $hashedPassword): bool
    {
        return Hash::check($password, $hashedPassword);
    }
}
