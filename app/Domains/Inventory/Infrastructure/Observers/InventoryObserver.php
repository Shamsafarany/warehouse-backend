<?php

namespace App\Domains\Inventory\Infrastructure\Observers;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Infrastructure\Services\InventoryLoggerService;

class InventoryObserver
{
    public function __construct(protected InventoryLoggerService $logger) {}

    public function created(Inventory $inventory): void
    {
        $this->logger->logInventoryActivity('Inventory record provisioned', $inventory);
    }

    public function updated(Inventory $inventory): void
    {
        $this->logger->logInventoryActivity('Stock or reserved quantity modified', $inventory);
    }
}