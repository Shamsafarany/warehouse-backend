<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Domains\Inventory\Domain\Exceptions\InsufficientStockException;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AdjustStockAction
{
    public function execute(Inventory $inventory, array $data): Inventory
    {
        Gate::authorize('update', $inventory);

        return DB::transaction(function () use ($inventory, $data) {
            $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->firstOrFail();
            $type = $data['type'];
            $quantity = (int) $data['quantity'];
            $currentStock = $inventory->stock_quantity;

            if ($type === 'out') {
                if ($quantity > $currentStock) {
                    throw new InsufficientStockException();
                }
                $newStock = $currentStock - $quantity;
            } else {
                $newStock = $currentStock + $quantity;
            }

            $inventory->update(['stock_quantity' => $newStock,]);

            $inventory->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'notes' => $data['notes'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
            ]);

            return $inventory->fresh(['product', 'stockMovements']);
        });
    }
}