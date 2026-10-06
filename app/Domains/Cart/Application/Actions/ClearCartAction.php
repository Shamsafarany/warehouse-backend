<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute(User $user): Cart
    {
        return DB::transaction(function () use ($user) {
            
            //find cart
            $cart = Cart::where('user_id', $user->id)->firstOrFail();
            if (!$cart){
                return;
            }
                
            $cart->items()->delete();
            return $cart->fresh(['items.product']);
        });
    }
}