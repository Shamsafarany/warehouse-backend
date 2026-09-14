<?php

namespace App\Domains\Catalog\Application\Actions\Category;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Support\Facades\DB;

class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            return Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        });
    }
}