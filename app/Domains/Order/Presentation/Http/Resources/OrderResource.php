<?php

namespace App\Domains\Order\Presentation\Http\Resources;

use App\Domains\Order\Presentation\Http\Resources\OrderItemsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'order',
            'id' => $this->id,
            'status' => $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            'total_amount' => $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'items' => OrderItemsResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}