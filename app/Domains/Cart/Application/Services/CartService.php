<?php
namespace App\Domains\Cart\Application\Services;

use App\Domains\Cart\Application\Actions\AddToCartAction;
use App\Domains\Cart\Application\Actions\ClearCartAction;
use App\Domains\Cart\Application\Actions\RemoveItemAction;
use App\Domains\Cart\Application\Actions\UpdateQuantityAction;
use App\Domains\Cart\Infrastructure\Models\Cart;

class CartService
{
    public function __construct(
        protected AddToCartAction $addToCartAction,
        protected UpdateQuantityAction $updateCartItemAction,
        protected RemoveItemAction $removeCartItemAction,
        protected ClearCartAction $clearCartAction
    ) {}

    public function getCart(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId])->load('items.product');
    }

    public function addItem(int $userId, int $productId, int $quantity): Cart
    {
        return $this->addToCartAction->execute($userId, $productId, $quantity);
    }

    public function updateItemQuantity(int $userId, string $cartItemId, int $newQuantity): Cart
    {
        return $this->updateCartItemAction->execute($userId, $cartItemId, $newQuantity);
    }

    public function removeItem(int $userId, string $cartItemId): Cart
    {
        return $this->removeCartItemAction->execute($userId, $cartItemId);
    }

    public function clearCart(int $userId) 
    {
        return $this->clearCartAction->execute($userId);
    }
}