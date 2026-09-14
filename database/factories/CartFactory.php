<?php

namespace Database\Factories;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()
        ];
    }
}
