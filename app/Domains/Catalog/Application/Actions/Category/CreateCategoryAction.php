<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        Gate::authorize('create', Category::class);
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }
}