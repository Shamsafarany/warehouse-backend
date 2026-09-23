<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Support\Facades\Gate;

class UpdateProductAction
{
    public function execute(Product $product, array $data): Product
    {
        Gate::authorize('update', $product);
        $product->update($data);
        
        return $product->fresh(['category', 'images', 'inventories']);
    }
}