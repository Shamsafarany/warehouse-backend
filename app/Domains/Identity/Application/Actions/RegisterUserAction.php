<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(array $data): array
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? UserRole::CUSTOMER->value,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}