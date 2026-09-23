<?php

namespace App\Domains\Catalog\Application\Services;

use App\Domains\Catalog\Application\Actions\Product\CreateProductAction;
use App\Domains\Catalog\Application\Actions\Product\UpdateProductAction;
use App\Domains\Catalog\Application\Actions\Product\DeleteProductAction;
use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected CreateProductAction $createProductAction,
        protected UpdateProductAction $updateProductAction,
        protected DeleteProductAction $deleteProductAction
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'images', 'inventories'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->createProductAction->execute($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->updateProductAction->execute($product, $data);
    }

    public function delete(Product $product): void
    {
        $this->deleteProductAction->execute($product);
    }
}