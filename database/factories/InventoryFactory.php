<?php

namespace Database\Factories;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::factory(),
            'stock_quantity' => fake()->numberBetween(10, 200),
            'reserved_quantity' => 0,
        ];
    }
}
