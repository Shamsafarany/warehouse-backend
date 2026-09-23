<?php

namespace App\Domains\Inventory\Presentation\Http\Controllers;
use App\Domains\Inventory\Presentation\Http\Requests\StoreInventoryRequest;
use App\Domains\Inventory\Presentation\Http\Requests\StoreStockAdjustRequest;
use App\Domains\Inventory\Presentation\Http\Requests\UpdateInventoryRequest;
use App\Domains\Inventory\Presentation\Http\Resources\InventoryResource;
use App\Domains\Inventory\Presentation\Http\Resources\InventoryCollection;
use App\Domains\Inventory\Application\Services\InventoryService;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected InventoryService $inventoryService) {}

    public function index(): JsonResponse
    {
        try {
            $inventories = $this->inventoryService->paginate();

            return $this->successResponse(
                new InventoryCollection($inventories),
                'تم استرجاع قائمة المخزون بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في استرجاع المخزون: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function show(Inventory $inventory): JsonResponse
    {
        try {
            $inventory->load(['product', 'stockMovements']);

            return $this->successResponse(
                new InventoryResource($inventory),
                'تم استرجاع تفاصيل المخزون بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في استرجاع تفاصيل المخزون: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        try {
            $updatedInventory = $this->inventoryService->update($inventory, $request->validated());

            return $this->successResponse(
                new InventoryResource($updatedInventory),
                'تم تحديث المخزون بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تحديث المخزون: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function adjust(StoreStockAdjustRequest $request, 
    Inventory $inventory): JsonResponse {
            $updatedInventory = $this->inventoryService->adjust($inventory, $request->validated());

        return $this->successResponse(
            new InventoryResource($updatedInventory),
            'تم تعديل المخزون وتسجيل الحركة بنجاح'
        );
        }
}