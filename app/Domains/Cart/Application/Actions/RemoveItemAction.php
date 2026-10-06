<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use Illuminate\Support\Facades\DB;

class RemoveItemAction
{
    public function execute(int $userId, string $cartItemId): Cart
    {
        return DB::transaction(function () use ($userId, $cartItemId) {
            
            //find cart
            $cart = Cart::where('user_id', $userId)->firstOrFail();

            //cart item 
            $cartItem = $cart->items()->where('id', $cartItemId)->firstOrFail();

            //Delete item
            $cartItem->delete();

            return $cart->fresh(['items.product']);
        });
    }
}