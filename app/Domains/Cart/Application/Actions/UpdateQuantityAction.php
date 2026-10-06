<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class UpdateQuantityAction
{
    public function execute(int $userId, string $cartItemId, int $newQuantity): Cart
    {
        return DB::transaction(function () use ($userId, $cartItemId, $newQuantity) {
            //find cart
            $cart = Cart::where('user_id', $userId)->firstOrFail();

            //find cart item
            $cartItem = $cart->items()->where('id', $cartItemId)->firstOrFail();

            if ($newQuantity <= 0) {
                $cartItem->delete();
                return $cart->fresh(['items.product']);
            }

            $inventory = Inventory::where('product_id', $cartItem->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($inventory->stock_quantity < $newQuantity) {
                throw new InsufficientStockException('The requested quantity exceeds available stock.');
            }

            $cartItem->update([
                'quantity' => $newQuantity,
            ]);

            return $cart->fresh(['items.product']);
        });
    }
}