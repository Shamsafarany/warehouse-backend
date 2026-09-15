<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Domain\Exceptions\CategoryHasProductsException;
use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteCategoryAction
{
    public function execute(Category $category): ?bool
    {
        Gate::authorize('delete', $category);
        if ($category->products()->exists()) {
            throw new CategoryHasProductsException();
        }
        return $category->delete();
    }
}