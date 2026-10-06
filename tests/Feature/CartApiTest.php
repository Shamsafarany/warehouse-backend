<?php

namespace Tests\Feature;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_view_their_cart()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'type',
                    'id',
                    'user_id',
                    'items',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم استرجاع السلة بنجاح',
                'data' => [
                    'type' => 'cart',
                    'user_id' => $user->id,
                ]
            ]);
    }

    #[Test]
    public function cart_is_lazily_created_on_first_access()
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);

        $this->actingAs($user)->getJson('/api/v1/cart')->assertOk();

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }

    #[Test]
    public function user_can_add_a_product_to_the_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 2,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/cart', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'status_code' => 201,
                'message' => 'تمت إضافة المنتج إلى السلة بنجاح',
                'data' => [
                    'type' => 'cart',
                ]
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    #[Test]
    public function adding_same_product_twice_aggregates_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        // Add product first time (quantity 2)
        $this->actingAs($user)->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        // Add product second time (quantity 3)
        $this->actingAs($user)->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertCreated();

        // Total quantity should be 5 in a single cart item record
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function adding_product_exceeding_stock_fails_validation()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 10, // Exceeds available stock of 5
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/cart', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status_code' => 409,
            ]);
    }

    #[Test]
    public function quantity_cannot_be_negative_or_zero()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'product_id' => $product->id,
            'quantity' => 0, // Invalid quantity
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/cart', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    #[Test]
    public function inactive_product_cannot_be_added_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_active' => false, // Inactive product
        ]);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 1,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/cart', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status_code' => 422,
            ]);
    }

    #[Test]
    public function user_can_update_cart_item_quantity_using_patch()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $payload = [
            'quantity' => 5,
        ];

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/cart/items/{$cartItem->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم تحديث كمية المنتج في السلة بنجاح',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function customer_cannot_modify_another_customer_cart_item()
    {
        $owner = User::factory()->create();
        $maliciousUser = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create(['user_id' => $owner->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Malicious user tries to update owner's cart item
        $response = $this->actingAs($maliciousUser)
            ->patchJson("/api/v1/cart/items/{$cartItem->id}", ['quantity' => 5]);

        // Should fail because the cart belongs to another user (results in 404/Not Found or 403)
        $response->assertNotFound();
    }

    #[Test]
    public function user_can_remove_a_single_item_from_the_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/cart/items/{$cartItem->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم إزالة المنتج من السلة بنجاح',
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    #[Test]
    public function user_can_empty_the_entire_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/cart');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم إفراغ السلة بنجاح',
            ]);

        $this->assertDatabaseEmpty('cart_items');
    }

    #[Test]
    public function unauthenticated_user_cannot_access_cart()
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertUnauthorized();
    }
}