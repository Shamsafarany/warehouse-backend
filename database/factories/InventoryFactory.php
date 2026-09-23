<?php

namespace Database\Factories;

use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stock_quantity' => fake()->numberBetween(10, 200),
            'reserved_quantity' => 0,
        ];
    }
}
