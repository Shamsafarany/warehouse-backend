<?php

namespace App\Domains\Inventory\Infrastructure\Models;

use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[UseFactory(StockMovementFactory::class)]
class StockMovement extends Model
{
    use HasUlids,  HasFactory;

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
