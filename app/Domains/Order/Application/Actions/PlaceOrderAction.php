<?php

namespace App\Domains\Order\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Domains\Inventory\Domain\Exceptions\ProductNotAvailableException;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Domain\Exceptions\EmptyCartException;
use App\Domains\Order\Domain\Events\OrderPlaced;
use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Support\Facades\DB;

class PlaceOrderAction
{
    public function execute(User $user, array $data = []): Order
    {
        return DB::transaction(function () use ($user, $data) {
            
            //get cart
            $cart = Cart::with(['items.product'])->where('user_id', $user->id)->first();

            // validate cart
            if (!$cart || $cart->items->isEmpty()) {
                throw new EmptyCartException('لا يمكن إتمام الطلب، السلة فارغة.');
            }

            $totalAmount = 0;
            $itemsData = [];
            $inventoriesToUpdate = [];

            //get cart items
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // Validate product existence
                if (!$product) {
                    throw new ProductNotAvailableException('بعض المنتجات في سلتك لم تعد متوفرة.');
                }

                // Validate product active state
                if (!$product->is_active) {
                    throw new ProductNotAvailableException("المنتج '{$product->name}' غير فعال حالياً.");
                }

                // Lock inventory row to prevent concurrent race conditions
                $inventory = Inventory::where('product_id', $product->id)->lockForUpdate()->first();

                if (!$inventory || $inventory->stock_quantity < $cartItem->quantity) {
                    throw new InsufficientStockException("الكمية المطلوبة للمنتج '{$product->name}' تتجاوز المخزون المتاح.");
                }

                $unitPrice = $product->price;
                $lineSubtotal = $unitPrice * $cartItem->quantity;
                $totalAmount += $lineSubtotal;

                // Prepare item payload matching OrderItem fillable fields
                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $lineSubtotal,
                ];

                // Keep track of inventory models to decrement stock after validation passes
                $inventories[] = [
                    'model' => $inventory,
                    'quantity' => $cartItem->quantity,
                ];
            }

            // create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
            ]);

            // create order items
            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // update stock levels
            foreach ($inventories as $inv) {
                $inv['model']->decrement('stock_quantity', $inv['quantity']);
            }

            //clean cart
            $cart->items()->delete();
            $cart->delete();

            // 8. Dispatch the OrderPlaced event for asynchronous listeners
            //event(new OrderPlaced($order));

            return $order->load('items.product');
        });
    }
}