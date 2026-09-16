<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        Gate::authorize('update', $category);
        $category->update([
            'name' => $data['name']?? null,
            'description' => $data['description'] ?? $category->description,
        ]);

        return $category->fresh();
    }
}