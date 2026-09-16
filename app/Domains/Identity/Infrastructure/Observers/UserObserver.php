<?php

namespace App\Domains\Identity\Infrastructure\Observers;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Identity\Infrastructure\Services\AuthLoggerService;

class UserObserver
{
    public function __construct(protected AuthLoggerService $authLogger) {}
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->authLogger->logRegistration($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if the password was changed
        if ($user->isDirty('password')) {
            $this->authLogger->logPasswordUpdate($user);
        }

        // Check if other profile details changed
        if ($user->isDirty(['first_name', 'last_name', 'email'])) {
            $this->authLogger->logProfileUpdate($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->authLogger->logAccountDeletion($user);
    }
}
