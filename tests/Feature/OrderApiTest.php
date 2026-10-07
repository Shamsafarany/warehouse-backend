<?php

namespace Tests\Feature;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_order_can_be_cancelled(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['price' => 100.00]);
        Inventory::factory()->create(['product_id' => $product->id, 'stock_quantity' => 10]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 2,
            'subtotal' => 200.00,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', OrderStatus::CANCELLED->value);
    }

    public function test_non_pending_order_cannot_be_cancelled(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_cancellation_restores_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['price' => 100.00]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'stock_quantity' => 5]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 3,
            'subtotal' => 300.00,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        // Stock restored from 5 + 3 = 8
        $this->assertEquals(8, $inventory->fresh()->stock_quantity);
    }

    public function test_cancellation_creates_an_in_stock_movement(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['price' => 100.00]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'stock_quantity' => 5]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 2,
            'subtotal' => 200.00,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'in',
            'quantity' => 2,
        ]);
    }

    public function test_missing_inventory_rolls_back_cancellation(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['price' => 100.00]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 2,
            'subtotal' => 200.00,
        ]);

        // Inventory record intentionally deleted/missing
        Inventory::where('product_id', $product->id)->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        // Fails with server exception when inventory resource is missing in action
        $response->assertStatus(500);
        $this->assertEquals(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_valid_status_transition_succeeds(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::CONFIRMED->value);
    }

    public function test_invalid_status_transition_fails(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                'status' => OrderStatus::DELIVERED->value, // Invalid direct jump
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_non_admin_cannot_change_status(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        $response->assertStatus(403);
    }

    public function test_concurrent_cancellation_cannot_restore_stock_twice(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['price' => 100.00]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'stock_quantity' => 10]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 3,
            'subtotal' => 300.00,
        ]);

        // First cancellation
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel")
            ->assertStatus(200);

        $this->assertEquals(13, $inventory->fresh()->stock_quantity);

        // Attempting second cancellation on already cancelled order should fail state machine check
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(422);
        $this->assertEquals(13, $inventory->fresh()->stock_quantity); // Stock remains unchanged
    }

    public function test_order_placement_and_inventory_decrement_is_atomic(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $product = Product::factory()->create(['is_active' => true, 'price' => 100.00]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'stock_quantity' => 1]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 5, // More than stock (triggers failure)
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders');

        $response->assertStatus(422);

        // Verify atomicity: stock is untouched and no order was created
        $this->assertEquals(1, $inventory->fresh()->stock_quantity);
        $this->assertDatabaseCount('orders', 0);
    }
}