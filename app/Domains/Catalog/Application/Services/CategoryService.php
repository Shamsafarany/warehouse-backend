<?php

namespace App\Domains\Catalog\Application\Services;

use App\Domains\Catalog\Application\Actions\Category\CreateCategoryAction;
use App\Domains\Catalog\Application\Actions\Category\UpdateCategoryAction;
use App\Domains\Catalog\Application\Actions\Category\DeleteCategoryAction;
use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        protected CreateCategoryAction $createAction,
        protected UpdateCategoryAction $updateAction,
        protected DeleteCategoryAction $deleteAction
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::withCount('products')->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return $this->createAction->execute($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->updateAction->execute($category, $data);
    }

    public function delete(Category $category): ?bool
    {
        return $this->deleteAction->execute($category);
    }
}