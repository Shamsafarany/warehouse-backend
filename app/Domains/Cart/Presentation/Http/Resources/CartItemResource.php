<?php

namespace App\Domains\Cart\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type'       => 'cart_item',
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'quantity'   => (int) $this->quantity,
            'product'    => $this->whenLoaded('product'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}