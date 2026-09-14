<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Domain\Exceptions\CategoryHasProductsException;
use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteCategoryAction
{
    public function execute(Category $category): ?bool
    {
        if ($category->products()->exists()) {
            throw new CategoryHasProductsException();
        }

        return DB::transaction(function () use ($category) {
            return $category->delete();
        });
    }
}