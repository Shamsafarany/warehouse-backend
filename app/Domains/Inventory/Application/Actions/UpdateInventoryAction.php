<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Support\Facades\Gate;

class UpdateInventoryAction
{
    public function execute(Inventory $inventory, array $data): Inventory
    {
        Gate::authorize('update', $inventory);

        $inventory->update($data);

        return $inventory->fresh(['product', 'stockMovements']);
    }
}