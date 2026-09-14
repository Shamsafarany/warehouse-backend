<?php

namespace Database\Factories;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $faker = fake('ar_SA');
        $price = fake()->randomFloat(2, 50, 5000);

        return [
            'category_id' => Category::factory(),
            'name' => $faker->unique()->words(3, true),
            'description' => $faker->paragraph(),
            'price' => $price,
            'is_active' => true,
        ];
    }
}
