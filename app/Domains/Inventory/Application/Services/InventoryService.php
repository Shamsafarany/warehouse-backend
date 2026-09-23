<?php

namespace App\Domains\Inventory\Application\Services;

use App\Domains\Catalog\Application\Actions\DeleteInventoryAction;
use App\Domains\Inventory\Application\Actions\AdjustStockAction;
use App\Domains\Inventory\Application\Actions\CreateInventoryAction;
use App\Domains\Inventory\Application\Actions\UpdateInventoryAction;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryService
{
    public function __construct(
        protected CreateInventoryAction $createInventoryAction,
        protected UpdateInventoryAction $updateInventoryAction,
        protected AdjustStockAction $adjustStockAction
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Inventory::with(['product', 'stockMovements'])
            ->latest()
            ->paginate($perPage);
    }
    
    public function update(Inventory $inventory, array $data): Inventory
    {
        return $this->updateInventoryAction->execute($inventory, $data);
    }

    public function adjust(Inventory $inventory, array $data): Inventory
    {
        return $this->adjustStockAction->execute($inventory, $data);
    }
}