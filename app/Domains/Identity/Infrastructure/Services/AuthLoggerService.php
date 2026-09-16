<?php

namespace App\Domains\Identity\Infrastructure\Services;

use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\Log;

class AuthLoggerService
{
    public function logRegistration(User $user): void
    {
        Log::channel('auth')->info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role?->value ?? $user->role,
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logPasswordUpdate(User $user): void
    {
        Log::channel('auth')->warning('Security event: Password updated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logProfileUpdate(User $user): void
    {
        Log::channel('auth')->info('User profile updated', [
            'user_id' => $user->id,
            'changes' => $user->getChanges(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logAccountDeletion(User $user): void
    {
        Log::channel('auth')->warning('Security event: User account deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}