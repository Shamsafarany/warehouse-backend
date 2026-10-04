<?php

namespace App\Domains\Catalog\Infrastructure\Observers;

use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Infrastructure\Services\CatalogLoggerService;

class ProductObserver
{
    public function __construct(protected CatalogLoggerService $logger) {}

    public function created(Product $product): void
    {
        $this->logger->logProductActivity('Created successfully with initial inventory', $product);
    }

    public function updated(Product $product): void
    {
        $this->logger->logProductActivity('Updated details', $product);
    }

    public function deleted(Product $product): void
    {
        $this->logger->logProductActivity('Deleted', $product);
    }
}