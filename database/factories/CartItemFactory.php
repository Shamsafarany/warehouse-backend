<?php

namespace Database\Factories;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Cart\Infrastructure\Models\CartItem;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
