<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): User
    {
        
        $user->update($data);

        return $user->fresh();
    }
}