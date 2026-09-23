<?php

namespace App\Domains\Inventory\Presentation\Http\Resources;

use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'inventory',
            'id' => $this->id,
            'product_id' => $this->product_id,
            'stock_quantity' => (int) $this->stock_quantity,
            'reserved_quantity' => (int) $this->reserved_quantity,
            'available_quantity' => (int) ($this->stock_quantity - $this->reserved_quantity),
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
