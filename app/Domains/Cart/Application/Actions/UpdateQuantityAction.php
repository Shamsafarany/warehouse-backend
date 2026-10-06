<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateQuantityAction
{
    public function execute(User $user, string|int $cartItemId, array $data): Cart
    {
        $newQuantity = $data['quantity'];

        return DB::transaction(function () use ($user, $cartItemId, $newQuantity) {
            $cart = Cart::where('user_id', $user->id)->firstOrFail();
            
            $cartItem = $cart->items()->where('id', $cartItemId)->firstOrFail();

            if ($newQuantity > 0) {
                $inventory = Inventory::where('product_id', $cartItem->product_id)->lockForUpdate()->firstOrFail();

                if ($inventory->stock_quantity < $newQuantity) {
                    throw new InsufficientStockException('The requested quantity exceeds available stock.');
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $cartItem->delete();
            }

            return $cart->fresh(['items.product']);
        });
    }
}