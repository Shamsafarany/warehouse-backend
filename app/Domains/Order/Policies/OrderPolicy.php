<?php
namespace App\Domains\Order\Presentation\Policies;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Infrastructure\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewAnyAdmin(User $user): bool
    {
        return $user->is_admin ?? false;
    }

    public function view(User $user, Order $order): bool
    {
        return ($user->is_admin ?? false)
            || $user->id === $order->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->is_admin ?? false;
    }

    public function cancel(User $user, Order $order): bool
    {
        return ($user->is_admin ?? false)
            || $user->id === $order->user_id;
    }
}