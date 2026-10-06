<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute(int $userId): Cart
    {
        return DB::transaction(function () use ($userId) {
            
            //find cart
            $cart = Cart::where('user_id', $userId)->firstOrFail();
            if (!$cart){
                return;
            }
                
            $cart->items()->delete();
            return $cart->fresh(['items.product']);
        });
    }
}