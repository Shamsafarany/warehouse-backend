<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User as ModelsUser;


class DeleteUserAccountAction
{
    public function execute(ModelsUser $user): void
    {
        $user->tokens()->delete();
        
        $user->delete();
    }
}