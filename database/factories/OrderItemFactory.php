<?php

namespace Database\Factories;

use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Order\Infrastructure\Models\OrderItem;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => function (array $attributes) {
                return Product::find($attributes['product_id'])?->name ?? 'منتج معدني';
            },
            'unit_price' => function (array $attributes) {
                return Product::find($attributes['product_id'])?->price ?? 100.00;
            },
            'quantity' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                $price = $product?->price ?? 100.00;
                $quantity = fake()->numberBetween(1, 3);
                return $quantity;
            },
            'subtotal' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                $price = $product?->price ?? 100.00;
                return $price * 1;
            },
        ];
    }
}
