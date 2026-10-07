<?php
namespace App\Domains\Order\Infrastructure\Services;

use App\Domains\Order\Infrastructure\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderLoggerService
{
    public function logCreated(Order $order): void
    {
        Log::channel('customer')->info('Order placed successfully.', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'status' => $order->status?->value ?? $order->status,
        ]);
    }

    public function logStatusChanged(Order $order, string $oldStatus, string $newStatus): void
    {
        Log::channel('customer')->info('Order status changed.', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function logCancelled(Order $order): void
    {
        Log::channel('customer')->warning('Order cancelled and inventory restored.', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }
}