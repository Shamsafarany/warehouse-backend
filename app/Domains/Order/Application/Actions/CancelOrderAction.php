<?php

namespace App\Domains\Order\Application\Actions;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Domain\StateMachine\OrderStateMachine;
use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelOrderAction
{
    public function __construct(
        protected OrderStateMachine $stateMachine
    ) {}

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            $this->stateMachine->transition(
                $order,
                OrderStatus::CANCELLED
            );

            foreach ($order->items as $item) {

                $inventory = Inventory::query()
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new RuntimeException(
                        "Inventory not found for product {$item->product_id}."
                    );
                }

                $inventory->increment(
                    'stock_quantity',
                    $item->quantity
                );

                $inventory->stockMovements()->create([
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'notes' => "Order cancelled #{$order->id}",
                    'reference_id' => $order->id,
                ]);
            }

            $order->save();

            return $order->load('items.product');
        });
    }
}