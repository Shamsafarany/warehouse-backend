<?php

namespace Database\Factories;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $faker = fake('ar_SA');
        
        $address = [
            'street' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => 'دمشق',
            'postal_code' => $faker->postcode(),
            'country' => 'سوريا',
        ];

        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['pending', 'processing', 'completed']),
            'total_amount' => 0.00,
            'shipping_address' => $address,
            'billing_address' => $address,
        ];
    }
}
