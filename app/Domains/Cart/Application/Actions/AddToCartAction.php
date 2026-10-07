<?php

namespace App\Domains\Cart\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Inventory\Domain\Exceptions\ProductNotAvailableException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddToCartAction
{
    public function execute(User $user, array $data): Cart
    {
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw new ProductNotAvailableException('المنتج غير فعال ولا يمكن إضافته إلى السلة.');
        }

        return DB::transaction(function () use ($user, $productId, $quantity) {
            
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            $existingQuantity = $cartItem ? $cartItem->quantity : 0;
            $totalRequestedQuantity = $existingQuantity + $quantity;

            $inventory = Inventory::where('product_id', $productId)->lockForUpdate()->firstOrFail();

            if ($inventory->stock_quantity < $totalRequestedQuantity) {
                throw new InsufficientStockException('The requested quantity exceeds available stock.');
            }

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