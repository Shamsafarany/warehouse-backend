<?php

namespace App\Domains\Order\Domain\StateMachine;

use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Domain\Exceptions\InvalidOrderStatusTransitionException;
use App\Domains\Order\Infrastructure\Models\Order;

class OrderStateMachine
{
    public function transition(Order $order, OrderStatus $newStatus): void
    {
        if (!$this->canTransition($order->status, $newStatus)) {
            throw new InvalidOrderStatusTransitionException(
                "لا يمكن تغيير حالة الطلب من {$order->status->value} إلى {$newStatus->value}."
            );
        }

        $order->status = $newStatus;
    }

    public function canTransition(
        OrderStatus $current,
        OrderStatus $new
    ): bool {
        return match ($current) {
            OrderStatus::PENDING =>
                in_array($new, [
                    OrderStatus::CONFIRMED,
                    OrderStatus::CANCELLED,
                ], true),

            OrderStatus::CONFIRMED =>
                in_array($new, [
                    OrderStatus::PROCESSING,
                    OrderStatus::CANCELLED,
                ], true),

            OrderStatus::PROCESSING =>
                $new === OrderStatus::SHIPPED,

            OrderStatus::SHIPPED =>
                $new === OrderStatus::DELIVERED,

            OrderStatus::DELIVERED,
            OrderStatus::CANCELLED => false,
        };
    }
}