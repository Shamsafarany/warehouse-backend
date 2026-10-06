<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class AddToCartAction
{
    public function execute(int $userId, int $productId, int $quantity): Cart
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {
            
            //get - create cart
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            //find cart item
            $cartItem = $cart->items()->where('product_id', $productId)->first();

            $existingQuantity = $cartItem ? $cartItem->quantity : 0;
            $totalRequestedQuantity = $existingQuantity + $quantity;

            //lock inventory
            $inventory = Inventory::where('product_id', $productId)->lockForUpdate()->firstOrFail();

            //check stock
            if ($inventory->stock_quantity < $totalRequestedQuantity) {
                throw new InsufficientStockException('The requested quantity exceeds available stock.');
            }

            //create - update cart item
            if ($cartItem) {
                $cartItem->update(['quantity' => $totalRequestedQuantity]);
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            return $cart->fresh(['items.product']);
        });
    }
}