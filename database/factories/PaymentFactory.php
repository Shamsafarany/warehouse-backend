<?php

namespace Database\Factories;

use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Payment\Infrastructure\Models\Payment;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => 0.00,
            'status' => 'succeeded',
            'gateway' => 'stripe',
            'transaction_reference' => 'ch_' . \Illuminate\Support\Str::random(24),
            'gateway_response' => ['status' => 'succeeded'],
        ];
    }
}
