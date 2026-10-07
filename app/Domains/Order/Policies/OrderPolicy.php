<?php
namespace App\Domains\Order\Policies;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Infrastructure\Models\Order;

class OrderPolicy
{
    public function viewAnyAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Order $order): bool
    {
        return $user->role === 'admin'
            || $user->id === $order->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->role === 'admin'
            || $user->id === $order->user_id;
    }
}