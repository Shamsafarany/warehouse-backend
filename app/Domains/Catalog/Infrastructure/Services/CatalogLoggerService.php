<?php

namespace App\Domains\Catalog\Infrastructure\Services;

use Illuminate\Support\Facades\Log;

class CatalogLoggerService
{
    public function logCategoryActivity(string $action, $category): void
    {
        Log::channel('admin')->info("Catalog [Category]: {$action}", [
            'category_id' => $category->id ?? null,
            'name' => $category->name ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logProductActivity(string $action, $product): void
    {
        Log::channel('admin')->info("Catalog [Product]: {$action}", [
            'product_id' => $product->id ?? null,
            'name' => $product->name ?? null,
            'price' => $product->price ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

}