<?php

namespace App\Domains\Order\Application\Actions;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Domains\Inventory\Domain\Exceptions\ProductNotAvailableException;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Domain\Exceptions\EmptyCartException;
use App\Domains\Order\Domain\Events\OrderPlaced;
use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlaceOrderAction
{
    public function execute(User $user, array $data = []): Order
    {
        // 1. Fetch user cart with items and products
        $cart = Cart::with(['items.product'])->where('user_id', $user->id)->first();

        // 2. Check if cart is empty
        if (!$cart || $cart->items->isEmpty()) {
            throw new EmptyCartException('لا يمكن إتمام الطلب، السلة فارغة.');
        }

        try {
            // 3. Begin Transaction
            $order = DB::transaction(function () use ($user, $data, $cart) {
                $totalAmount = 0;
                $orderItemsData = [];
                $inventoryActions = [];

                // 4. Validate products, lock inventory, and prepare snapshots
                foreach ($cart->items as $cartItem) {
                    $product = $cartItem->product;

                    if (!$product) {
                        throw new ProductNotAvailableException('بعض المنتجات في سلتك لم تعد متوفرة.');
                    }

                    if (!$product->is_active) {
                        throw new ProductNotAvailableException("المنتج '{$product->name}' غير فعال حالياً.");
                    }

                    // Pessimistic locking to prevent race conditions
                    $inventory = Inventory::where('product_id', $product->id)->lockForUpdate()->first();

                    if (!$inventory || $inventory->stock_quantity < $cartItem->quantity) {
                        throw new InsufficientStockException("الكمية المطلوبة للمنتج '{$product->name}' تتجاوز المخزون المتاح.");
                    }

                    $unitPrice = $product->price;
                    $lineSubtotal = $unitPrice * $cartItem->quantity;
                    $totalAmount += $lineSubtotal;

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $unitPrice,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $lineSubtotal,
                    ];

                    $inventoryActions[] = [
                        'inventory' => $inventory,
                        'quantity' => $cartItem->quantity,
                    ];
                }

                // 5. Create the Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => OrderStatus::PENDING,
                    'total_amount' => $totalAmount,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'billing_address' => $data['billing_address'] ?? null,
                ]);

                // 6. Create Order Items
                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                // 7. Decrease inventory and record stock movements
                foreach ($inventoryActions as $action) {
                    $inventory = $action['inventory'];
                    $quantity = $action['quantity'];

                    // Decrement stock quantity
                    $inventory->decrement('stock_quantity', $quantity);

                    // Record audit stock movement
                    $inventory->stockMovements()->create([
                        'type' => 'out',
                        'quantity' => $quantity,
                        'notes' => "Order placed #{$order->id}",
                        'reference_id' => $order->id,
                    ]);
                }

                // 8. Clear the Cart
                $cart->items()->delete();

                return $order;
            });

            // 9. Dispatch Order Placed event strictly AFTER successful transaction commit
            //event(new OrderPlaced($order));

            return $order->load('items.product');

        } catch (Throwable $e) {
            Log::error('Order placement transaction failed', [
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);

            throw $e;
        }
    }
}