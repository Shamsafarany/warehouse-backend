<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): User
    {
        
        $user->update(array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
        ]));

        return $user;
    }
}