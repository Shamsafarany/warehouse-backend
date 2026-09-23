<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Catalog\Infrastructure\Models\Product;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; 
    }

    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}