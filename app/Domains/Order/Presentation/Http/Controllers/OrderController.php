<?php

namespace App\Domains\Order\Presentation\Http\Controllers;

use App\Domains\Order\Application\Actions\CancelOrderAction;
use App\Domains\Order\Application\Actions\ChangeOrderStatusAction;
use App\Domains\Order\Application\Actions\PlaceOrderAction;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Order\Presentation\Http\Requests\ChangeStatusRequest;
use App\Domains\order\Presentation\Http\Requests\PlaceOrderRequest;
use App\Domains\Order\Presentation\Http\Resources\OrderResource;
use App\Http\Controllers\Concerns\ApiResponse as ConcernsApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OrderCollection;

class OrderController
{
    use ConcernsApiResponse;

    public function __construct(
        protected PlaceOrderAction $placeOrderAction
    ) {}

    public function orderHistory(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        $perPage = min(
            (int) $request->input('per_page', 15),
            100
        );


        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(
            new OrderCollection($orders),
            'تم استرجاع سجل الطلبات بنجاح'
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyAdmin', Order::class);

        $query = Order::query()
            ->with(['user', 'items.product'])
            ->latest();

        /*
         * GET /api/v1/admin/orders?status=pending
         */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        $orders = $query->paginate(15);

        return $this->successResponse(
            OrderResource::collection($orders),
            'تم استرجاع كافة طلبات النظام بنجاح'
        );
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        Gate::authorize('create', Order::class);

        $order = $this->placeOrderAction->execute(
            user: $request->user(),
            data: $request->validated()
        );

        return $this->successResponse(
            new OrderResource($order),
            'تم إنشاء الطلب بنجاح',
            Response::HTTP_CREATED
        );
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load('items.product');

        return $this->successResponse(
            new OrderResource($order),
            'تم استرجاع تفاصيل الطلب بنجاح'
        );
    }
    public function cancel(
        Order $order,
        CancelOrderAction $action
    ): JsonResponse {
        Gate::authorize('cancel', $order);

        $cancelledOrder = $action->execute($order);

        return $this->successResponse(
            new OrderResource($cancelledOrder),
            'تم إلغاء الطلب واسترجاع المخزون بنجاح'
        );
    }

    public function updateStatus(
        ChangeStatusRequest $request,
        Order $order,
        ChangeOrderStatusAction $action
    ): JsonResponse {
        Gate::authorize('updateStatus', $order);

        $newStatus = OrderStatus::from(
            $request->validated('status')
        );

        $updatedOrder = $action->execute(
            $order,
            $newStatus
        );

        return $this->successResponse(
            new OrderResource($updatedOrder),
            'تم تحديث حالة الطلب بنجاح'
        );
    }
}