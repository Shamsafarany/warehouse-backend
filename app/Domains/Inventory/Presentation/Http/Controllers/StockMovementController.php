<?php

namespace App\Domains\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Presentation\Http\Resources\StockMovementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Concerns\ApiResponse;

class StockMovementController extends Controller
{
    use ApiResponse;

    public function index(Inventory $inventory): JsonResponse
    {
        Gate::authorize('view', $inventory);

        $movements = $inventory->stockMovements()->latest()->paginate(15);

        return $this->successResponse(
            StockMovementResource::collection($movements),
            'تم استرجاع سجل حركات المخزون بنجاح'
        );
    }
}