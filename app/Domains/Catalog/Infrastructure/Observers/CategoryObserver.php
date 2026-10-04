<?php

namespace App\Domains\Catalog\Infrastructure\Observers;

use App\Domains\Catalog\Infrastructure\Services\CatalogLoggerService;
use App\Domains\Catalog\Infrastructure\Models\Category;

class CategoryObserver
{
    public function __construct(protected CatalogLoggerService $logger) {}

    public function created(Category $category): void
    {
        $this->logger->logCategoryActivity('Created successfully', $category);
    }

    public function updated(Category $category): void
    {
        $this->logger->logCategoryActivity('Updated successfully', $category);
    }

    public function deleted(Category $category): void
    {
        $this->logger->logCategoryActivity('Deleted successfully', $category);
    }
}