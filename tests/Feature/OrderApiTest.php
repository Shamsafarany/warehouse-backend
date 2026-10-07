<?php

namespace Tests\Feature;

use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Order\Domain\Enums\OrderStatus;
use App\Domains\Order\Infrastructure\Models\Order;
use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Product $product;
    protected Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->product = Product::factory()->create([
            'price' => 150.00,
            'is_active' => true,
        ]);

        $this->inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'stock_quantity' => 10,
        ]);
    }

    // ==========================================
    // PLACE ORDER TESTS
    // ==========================================

    /** @test */
    public function empty_cart_fails()
    {
        Sanctum::actingAs($this->user);

        // Cart exists but has no items
        Cart::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function inactive_product_fails()
    {
        Sanctum::actingAs($this->user);

        $this->product->update(['is_active' => false]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function insufficient_stock_fails()
    {
        Sanctum::actingAs($this->user);

        $this->inventory->update(['stock_quantity' => 2]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5, // Requesting more than available
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        // Ensure stock was not touched due to transaction rollback
        $this->assertEquals(2, $this->inventory->fresh()->stock_quantity);
    }

    /** @test */
    public function correct_server_side_price_is_used_and_order_items_contain_snapshots()
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_amount', 300.00) // 150 * 2
            ->assertJsonPath('data.items.0.unit_price', 150.00)
            ->assertJsonPath('data.items.0.subtotal', 300.00)
            ->assertJsonPath('data.items.0.product_name', $this->product->name);
    }

    /** @test */
    public function inventory_decreases_stock_movement_is_created_and_cart_is_emptied()
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(201);

        // Inventory decreased (10 - 3 = 7)
        $this->assertEquals(7, $this->inventory->fresh()->stock_quantity);

        // Stock movement recorded ('out')
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $this->inventory->id,
            'type' => 'out',
            'quantity' => 3,
        ]);

        // Cart emptied
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    // ==========================================
    // CANCEL ORDER TESTS
    // ==========================================

    /** @test */
    public function customer_can_cancel_pending_order_which_restores_inventory_creates_return_movement_and_updates_status()
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 150.00,
            'quantity' => 4,
            'subtotal' => 600.00,
        ]);

        // Adjust inventory to reflect prior checkout deduction
        $this->inventory->update(['stock_quantity' => 6]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', OrderStatus::CANCELLED->value);

        // Inventory restored (6 + 4 = 10)
        $this->assertEquals(10, $this->inventory->fresh()->stock_quantity);

        // Return stock movement created ('in')
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $this->inventory->id,
            'type' => 'in',
            'quantity' => 4,
        ]);
    }

    /** @test */
    public function customer_cannot_cancel_shipped_order()
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::SHIPPED,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // ==========================================
    // CHANGE STATUS TESTS
    // ==========================================

    /** @test */
    public function admin_can_make_valid_transitions()
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Pending -> Confirmed
        $response = $this->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::CONFIRMED->value);
    }

    /** @test */
    public function admin_cannot_make_invalid_transitions()
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Pending -> Delivered (Invalid direct jump)
        $response = $this->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::DELIVERED->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function normal_user_cannot_change_status()
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function delivered_order_cannot_change()
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::DELIVERED,
        ]);

        $response = $this->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::PROCESSING->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function cancelled_order_cannot_change()
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CANCELLED,
        ]);

        $response = $this->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}