<?php

namespace App\Domains\Order\Application\Actions;

use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Domain\StateMachine\OrderStateMachine;
use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Support\Facades\DB;

class ChangeOrderStatusAction
{
    public function __construct(
        protected OrderStateMachine $stateMachine
    ) {}

    public function execute(
        Order $order,
        OrderStatus $newStatus
    ): Order {
        return DB::transaction(function () use ($order, $newStatus) {

            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->stateMachine->transition(
                $order,
                $newStatus
            );

            $order->save();

            return $order->load('items.product');
        });
    }
}