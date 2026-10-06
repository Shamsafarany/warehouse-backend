<?php

namespace App\Domains\Cart\Policies;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Identity\Infrastructure\Models\User;

class CartPolicy
{
    public function view(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    public function update(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    public function empty(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

}