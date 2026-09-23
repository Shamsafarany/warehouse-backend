<?php

namespace Tests\Feature;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_view_product_list(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'type',
                            'id',
                            'category', 
                            'name',
                            'description',
                            'price',
                            'is_active',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages',
                    ]
                ]
            ]);
    }

    public function test_anyone_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ]
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'category_id' => $category->id,
                'name' => 'New Laptop',
                'description' => 'High performance laptop',
                'price' => 1200.50,
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'تم إنشاء المنتج بنجاح',
                'data' => [
                    'name' => 'New Laptop',
                    'price' => 1200.50,
                ]
            ]);
    }

    public function test_customer_cannot_create_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'category_id' => $category->id,
                'name' => 'Forbidden Product',
                'price' => 100.00,
            ]);

        // Returns 403 because admin middleware or policy blocks non-admins
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم حذف المنتج بنجاح',
            ]);

        $this->assertSoftDeleted($product);
    }
}