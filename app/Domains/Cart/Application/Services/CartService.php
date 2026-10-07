<?php

namespace App\Domains\Cart\Application\Services;

use App\Domains\Cart\Application\Actions\AddToCartAction;
use App\Domains\Cart\Application\Actions\ClearCartAction;
use App\Domains\Cart\Application\Actions\RemoveItemAction;
use App\Domains\Cart\Application\Actions\UpdateQuantityAction;
use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Support\Facades\Gate;

class CartService
{
    public function __construct(
        protected AddToCartAction $addToCartAction,
        protected UpdateQuantityAction $updateCartItemAction,
        protected RemoveItemAction $removeCartItemAction,
        protected ClearCartAction $emptyCartAction
    ) {}

    public function getCart(User $user): Cart
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        Gate::authorize('view', $cart);

        return $cart->load('items.product');
    }

    public function addItem(User $user, array $data): Cart
    {
        return $this->addToCartAction->execute($user, $data);
    }

    public function updateItemQuantity(User $user, string|int $cartItemId, array $data): Cart
    {
        return $this->updateCartItemAction->execute($user, $cartItemId, $data);
    }

    public function removeItem(User $user, string|int $cartItemId): Cart
    {
        return $this->removeCartItemAction->execute($user, $cartItemId);
    }

    public function emptyCart(User $user): Cart
    {
        return $this->emptyCartAction->execute($user);
    }
}