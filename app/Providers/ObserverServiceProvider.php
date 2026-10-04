<?php

namespace App\Providers;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Infrastructure\Observers\CategoryObserver;
use App\Domains\Catalog\Infrastructure\Observers\ProductObserver;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Identity\Infrastructure\Observers\UserObserver;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Infrastructure\Models\StockMovement;
use App\Domains\Inventory\Infrastructure\Observers\InventoryObserver;
use App\Domains\Inventory\Infrastructure\Observers\StockMovementObserver;
use Illuminate\Support\ServiceProvider;



class ObserverServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings or container singletons if needed
    }

    public function boot(): void
    {
        // Register Eloquent Observers
        User::observe(UserObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        Inventory::observe(InventoryObserver::class);
        StockMovement::observe(StockMovementObserver::class);
    }
}