<?php

namespace App\Domains\Inventory\Infrastructure\Observers;

use App\Domains\Inventory\Infrastructure\Models\StockMovement;
use App\Domains\Inventory\Infrastructure\Services\InventoryLoggerService;
class StockMovementObserver
{
    public function __construct(protected InventoryLoggerService $logger) {}

    public function created(StockMovement $movement): void
    {
        $this->logger->logStockMovement('Physical movement recorded', $movement);
    }
}