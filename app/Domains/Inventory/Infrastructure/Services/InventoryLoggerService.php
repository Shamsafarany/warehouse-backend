<?php

namespace App\Domains\Inventory\Infrastructure\Services;

use Illuminate\Support\Facades\Log;

class InventoryLoggerService
{
    public function logInventoryActivity(string $action, $inventory): void
    {
        Log::channel('admin')->info("Inventory: {$action}", [
            'inventory_id' => $inventory->id ?? null,
            'product_id' => $inventory->product_id ?? null,
            'stock_quantity' => $inventory->stock_quantity ?? null,
            'reserved_quantity' => $inventory->reserved_quantity ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logStockMovement(string $action, $movement): void
    {
        Log::channel('admin')->warning("StockMovement: {$action}", [
            'movement_id' => $movement->id ?? null,
            'inventory_id' => $movement->inventory_id ?? null,
            'type' => $movement->type ?? null,
            'quantity' => $movement->quantity ?? null,
            'notes' => $movement->notes ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}