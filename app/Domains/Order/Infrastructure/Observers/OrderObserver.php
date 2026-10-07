<?php

namespace App\Domains\Order\Infrastructure\Observers;

use App\Domains\Order\Application\Services\OrderLoggerService;
use App\Domains\Order\Infrastructure\Models\Order;

class OrderObserver
{
    public function __construct(protected OrderLoggerService $loggerService) {}

    public function created(Order $order): void
    {
        $this->loggerService->logCreated($order);
    }

    public function updated(Order $order): void
    {
        // Check if the status attribute was modified
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status')?->value ?? $order->getOriginal('status');
            $newStatus = $order->status?->value ?? $order->status;

            $this->loggerService->logStatusChanged($order, $oldStatus, $newStatus);

            // Specifically log cancellations if status transitioned to cancelled
            if ($newStatus === 'cancelled') {
                $this->loggerService->logCancelled($order);
            }
        }
    }
}