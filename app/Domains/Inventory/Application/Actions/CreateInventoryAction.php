<?php

namespace App\Domains\Inventory\Application\Actions;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Support\Facades\Gate;

class CreateInventoryAction
{
    public function execute(array $data): Inventory
    {
        Gate::authorize('create', Inventory::class);

        return Inventory::create($data);
    }
}