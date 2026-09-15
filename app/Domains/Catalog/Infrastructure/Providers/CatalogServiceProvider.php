<?php

namespace App\Domains\Catalog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Core\Policies\CategoryPolicy;
use App\Domains\Catalog\Policies\CategoryPolicy as PoliciesCategoryPolicy;

class CatalogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Category::class, PoliciesCategoryPolicy::class);
    }
}