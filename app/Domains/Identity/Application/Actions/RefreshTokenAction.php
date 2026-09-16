<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User;

class RefreshTokenAction
{
    public function execute(User $user): string
    {
        // Delete the current access token being used to hit the endpoint
        $user->currentAccessToken()->delete();

        // Create and return a new plain text token
        return $user->createToken('auth_token')->plainTextToken;
    }
}