<?php

namespace Tests\Unit;

use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Domain\StateMachine\OrderStateMachine;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Order\Domain\Exceptions\InvalidOrderStatusTransitionException;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    protected OrderStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new OrderStateMachine();
    }

    public function test_valid_transitions_are_allowed(): void
    {
        $order = new Order(['status' => OrderStatus::PENDING]);

        // PENDING -> CONFIRMED
        $this->stateMachine->transition($order, OrderStatus::CONFIRMED);
        $this->assertEquals(OrderStatus::CONFIRMED, $order->status);

        // CONFIRMED -> PROCESSING
        $this->stateMachine->transition($order, OrderStatus::PROCESSING);
        $this->assertEquals(OrderStatus::PROCESSING, $order->status);

        // PROCESSING -> SHIPPED
        $this->stateMachine->transition($order, OrderStatus::SHIPPED);
        $this->assertEquals(OrderStatus::SHIPPED, $order->status);

        // SHIPPED -> DELIVERED
        $this->stateMachine->transition($order, OrderStatus::DELIVERED);
        $this->assertEquals(OrderStatus::DELIVERED, $order->status);
    }

    public function test_cancellation_from_pending_is_allowed(): void
    {
        $order = new Order(['status' => OrderStatus::PENDING]);
        $this->stateMachine->transition($order, OrderStatus::CANCELLED);
        $this->assertEquals(OrderStatus::CANCELLED, $order->status);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);

        $order = new Order(['status' => OrderStatus::PENDING]);
        
        // Cannot jump directly from PENDING to DELIVERED
        $this->stateMachine->transition($order, OrderStatus::DELIVERED);
    }

    public function test_terminal_state_cannot_transition(): void
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);

        $order = new Order(['status' => OrderStatus::DELIVERED]);
        
        // Delivered is terminal
        $this->stateMachine->transition($order, OrderStatus::PROCESSING);
    }
}