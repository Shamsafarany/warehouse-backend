<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateProductAction
{
    public function execute(array $data): Product
    {
        Gate::authorize('create', Product::class);

        return DB::transaction(function () use ($data) {
            $stockQuantity = $data['stock_quantity'] ?? 0;
            unset($data['stock_quantity']);

            // Business Rule: If product is out of stock (0), is_active is set to false
            if (!isset($data['is_active'])) {
                $data['is_active'] = $stockQuantity > 0;
            }

            $product = Product::create($data);

            $product->inventories()->create([
                'stock_quantity' => $stockQuantity,
                'reserved_quantity' => 0,
            ]);

            return $product->fresh(['category', 'images', 'inventories']);
        });
    }
}