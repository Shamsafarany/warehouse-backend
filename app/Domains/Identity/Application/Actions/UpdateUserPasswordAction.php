<?php

namespace App\Domains\Identity\Application\Actions;

use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordAction
{
    public function execute(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}