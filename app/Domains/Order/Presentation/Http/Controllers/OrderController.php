<?php

namespace App\Domains\Order\Presentation\Controllers;

use App\Domains\Order\Application\Actions\PlaceOrderAction;
use App\Domains\Order\Presentation\Resources\OrderResource;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\order\Presentation\Requests\PlaceOrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(protected PlaceOrderAction $placeOrderAction) {}

    public function index(Request $request): JsonResponse
    {
        try {
            Gate::authorize('viewAny', Order::class);

            $orders = $request->user()
                ->orders()
                ->with('items.product')
                ->latest()
                ->paginate(15);

            return $this->successResponse(
                OrderResource::collection($orders),
                'تم استرجاع قائمة الطلبات بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'فشل في استرجاع الطلبات: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function adminIndex(Request $request): JsonResponse
    {
        try {
            Gate::authorize('viewAnyAdmin', Order::class);

            $orders = Order::with(['user', 'items.product'])
                ->latest()
                ->paginate(15);

            return $this->successResponse(
                OrderResource::collection($orders),
                'تم استرجاع كافة طلبات النظام بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'فشل في استرجاع طلبات النظام: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            Gate::authorize('create', Order::class);

            $order = $this->placeOrderAction->execute(
                user: $request->user(),
                data: $request->all()
            );

            return $this->successResponse(
                new OrderResource($order),
                'تم إنشاء الطلب بنجاح',
                Response::HTTP_CREATED
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'فشل في إنشاء الطلب: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show(Order $order): JsonResponse
    {
        try {
            Gate::authorize('view', $order);

            $order->load('items.product');

            return $this->successResponse(
                new OrderResource($order),
                'تم استرجاع تفاصيل الطلب بنجاح'
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'فشل في استرجاع تفاصيل الطلب: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}