<?php

namespace Database\Seeders;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Cart\Infrastructure\Models\CartItem;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Payment\Infrastructure\Models\Payment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (3 Admins, 5 Customers)
        $this->call(UserSeeder::class);

        // 2. Seed Static Warehouse Taxonomy & Products
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);

        // Fetch only customer users to simulate shopper behavior
        $customers = User::where('role', UserRole::CUSTOMER->value)->get();

        // 3. Seed active Carts for some customers
        $customers->take(3)->each(function ($customer) {
            $cart = Cart::factory()->for($customer)->create();
            Product::inRandomOrder()->take(2)->get()->each(function ($product) use ($cart) {
                CartItem::factory()->for($cart)->for($product)->create();
            });
        });

        $customers->each(function ($customer) {
            $order = Order::factory()->for($customer)->create();
            Payment::factory()->for($order)->create([
                'amount' => $order->total_amount,
            ]);
        });
    }
}
