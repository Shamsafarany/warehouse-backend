<?php

namespace App\Domains\Inventory\Policies;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Inventory\Infrastructure\Models\Inventory;

class InventoryPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(?User $user, Inventory $inventory): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->isAdmin();
    }
}