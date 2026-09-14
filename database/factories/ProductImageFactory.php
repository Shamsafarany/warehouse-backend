<?php

namespace Database\Factories;

use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Infrastructure\Models\ProductImage;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => 'https://picsum.photos/seed/' . fake()->uuid() . '/600/600',
            'is_primary' => false,
            'sort_order' => 0,
        ];
    }
}
