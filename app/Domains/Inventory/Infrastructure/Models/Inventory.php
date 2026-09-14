<?php

namespace App\Domains\Inventory\Infrastructure\Models;

use App\Domains\Catalog\Infrastructure\Models\Product;
use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(InventoryFactory::class)]
class Inventory extends Model
{
    use HasUlids,  HasFactory;

    protected $fillable = [
        'product_id',
        'stock_quantity',
        'reserved_quantity',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
