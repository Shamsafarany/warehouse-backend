<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\DB;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $category->description,
            ]);

            return $category->fresh();
        });
    }
}