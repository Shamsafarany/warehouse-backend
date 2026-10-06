<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveItemAction
{
    public function execute(User $user, string $cartItemId): Cart
    {
        return DB::transaction(function () use ($user, $cartItemId) {
            
            //find cart
            $cart = Cart::where('user_id', $user->id)->firstOrFail();

            //cart item 
            $cartItem = $cart->items()->where('id', $cartItemId)->firstOrFail();

            //Delete item
            $cartItem->delete();

            return $cart->fresh(['items.product']);
        });
    }
}