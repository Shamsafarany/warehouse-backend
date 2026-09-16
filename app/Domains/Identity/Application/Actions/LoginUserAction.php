<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function execute(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
        ]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}