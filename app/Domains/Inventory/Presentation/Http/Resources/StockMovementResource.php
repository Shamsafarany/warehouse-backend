<?php

namespace App\Domains\Inventory\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'stock_movement',
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'movement_type' => $this->type, // 'in' or 'out'
            'quantity' => (int) $this->quantity,
            'notes' => $this->notes,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}