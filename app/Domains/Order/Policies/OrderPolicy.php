<?php

namespace App\Domains\Order\Policies;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Infrastructure\Models\Order;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own order history list
    }

    /**
     * Determine whether the user can view the specific order.
     */
    public function view(User $user, Order $order): bool
    {
        // IDOR Prevention: Ensure the order belongs to the authenticated user
        return $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can create/place an order.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can place an order
    }

    /**
     * Determine whether the user can update or cancel the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Users can only modify/cancel pending orders that belong to them
        return $user->id === $order->user_id && $order->status === 'pending';
    }
}