<?php

namespace App\Domains\Cart\Presentation\Http\Controllers;

use App\Domains\Cart\Application\Services\CartService;
use App\Domains\Cart\Presentation\Http\Requests\AddToCartRequest;
use App\Domains\Cart\Presentation\Http\Requests\UpdateQuantityRequest;
use App\Domains\Cart\Presentation\Http\Resources\CartResource;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart(
            $request->user()
        );

        return $this->successResponse(
            new CartResource($cart),
            'تم استرجاع السلة بنجاح'
        );
    }


public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->addItem(
            user: $request->user(),
            data: $request->validated()
        );

        return $this->successResponse(
            new CartResource($cart),
            'تمت إضافة المنتج إلى السلة بنجاح',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateQuantityRequest $request, string $cartItemId): JsonResponse
    {
        $cart = $this->cartService->updateItemQuantity(
            user: $request->user(),
            cartItemId: $cartItemId,
            data: $request->validated()
        );

        return $this->successResponse(
            new CartResource($cart),
            'تم تحديث كمية المنتج في السلة بنجاح'
        );
    }

    public function destroy(Request $request, string $cartItemId): JsonResponse
    {
        $cart = $this->cartService->removeItem(
            user: $request->user(),
            cartItemId: $cartItemId
        );

        return $this->successResponse(
            new CartResource($cart),
            'تم إزالة المنتج من السلة بنجاح'
        );
    }

    public function empty(Request $request): JsonResponse
    {
        $cart = $this->cartService->emptyCart(
            $request->user()
        );

        return $this->successResponse(
            new CartResource($cart),
            'تم إفراغ السلة بنجاح'
        );
    }
}