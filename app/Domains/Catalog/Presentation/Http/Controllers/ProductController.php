<?php

namespace App\Domains\Catalog\Presentation\Http\Controllers;

use App\Domains\Catalog\Application\Services\ProductService;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Presentation\Http\Requests\StoreProductRequest;
use App\Domains\Catalog\Presentation\Http\Requests\UpdateProductRequest;
use App\Domains\Catalog\Presentation\Http\Resources\ProductResource;
use App\Domains\Catalog\Presentation\Http\Resources\ProductCollection;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(protected ProductService $productService)
    {}
    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->paginate();

            return $this->successResponse(
                new ProductCollection($products),
                'تم استرجاع قائمة المنتجات بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في استرجاع المنتجات: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function show(Product $product): JsonResponse
    {
        try {
            $product->load(['category', 'images', 'inventories']);

            return $this->successResponse(
                new ProductResource($product),
                'تم استرجاع تفاصيل المنتج بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في استرجاع المنتج: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            return $this->successResponse(
                new ProductResource($product),
                'تم إنشاء المنتج بنجاح',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في إنشاء المنتج: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->update($product, $request->validated());

            return $this->successResponse(
                new ProductResource($updatedProduct),
                'تم تحديث المنتج بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تحديث المنتج: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->delete($product);

            return $this->successResponse(
                null,
                'تم حذف المنتج بنجاح'
            );
        } catch (\Exception $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : Response::HTTP_BAD_REQUEST;
            return $this->errorResponse(
                'فشل في حذف المنتج: ' . $e->getMessage(),
                $status
            );
        }
    }
}