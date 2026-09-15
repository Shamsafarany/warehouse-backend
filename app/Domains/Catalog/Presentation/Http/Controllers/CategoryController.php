<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Presentation\Http\Requests\StoreCategoryRequest;
use App\Domains\Catalog\Presentation\Http\Requests\UpdateCategoryRequest;
use App\Domains\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Domains\Catalog\Application\Services\CategoryService;
use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->paginate(15);

        return $this->successResponse(
            CategoryResource::collection($categories),
            'تم استرجاع قائمة التصنيفات بنجاح',
            Response::HTTP_OK,
            [
                'X-Warehouse-Domain' => 'Catalog',
                'X-Total-Count' => $categories->total(),
            ]
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return $this->successResponse(
                new CategoryResource($category),
                'تم إنشاء التصنيف بنجاح',
                Response::HTTP_CREATED,
                [
                    'X-Warehouse-Domain' => 'Catalog',
                    'Location' => route('categories.show', $category->id),
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في إنشاء التصنيف: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST // 400
            );
        }
    }

    public function show(Category $category): JsonResponse
    {
        return $this->successResponse(
            new CategoryResource($category->loadCount('products')),
            'تم استرجاع بيانات التصنيف بنجاح'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $updatedCategory = $this->categoryService->update($category, $request->validated());

            return $this->successResponse(
                new CategoryResource($updatedCategory),
                'تم تحديث التصنيف بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تحديث التصنيف: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST // 400
            );
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return $this->successResponse(
            null,
            'تم حذف التصنيف بنجاح',
            Response::HTTP_OK
        );
    }
}