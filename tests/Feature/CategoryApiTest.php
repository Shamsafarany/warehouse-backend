<?php
namespace Tests\Feature;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_paginated_categories()
    {
        Category::factory()->create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        // If list is public, use /api/v1/categories. If admin-only, add actingAs($admin).
        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description']
                ],
                'links',
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم استرجاع قائمة التصنيفات بنجاح',
            ]);
    }

    #[Test]
    public function it_can_create_a_category_with_sanitized_inputs()
    {
        $payload = [
            'name' => '  <b>لفائف الصلب</b>  ',
            'description' => 'وصف تجريبي للتصنيف'
        ];
        
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        // Fixed: Single request properly authenticated and passing $payload
        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/categories', $payload);

        $response->assertCreated()
            ->assertHeader('X-Warehouse-Domain', 'Catalog')
            ->assertJson([
                'success' => true,
                'status_code' => 201,
                'message' => 'تم إنشاء التصنيف بنجاح',
                'data' => [
                    'name' => 'لفائف الصلب', // Tags stripped and trimmed
                    'description' => 'وصف تجريبي للتصنيف'
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'لفائف الصلب']);
    }

    #[Test]
    public function it_validates_required_fields_on_category_creation()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                'name' => ['حقل اسم التصنيف إجباري.']
            ]);
    }

    #[Test]
    public function it_can_show_a_single_category()
    {
        $category = Category::factory()->create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم استرجاع بيانات التصنيف بنجاح',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]
            ]);
    }

    #[Test]
    public function it_can_update_an_existing_category()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $category = Category::factory()->create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $payload = [
            'name' => 'الاسم المحدث',
            'description' => 'وصف جديد'
        ];

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/categories/{$category->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم تحديث التصنيف بنجاح',
                'data' => [
                    'name' => 'الاسم المحدث',
                    'description' => 'وصف جديد'
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'الاسم المحدث']);
    }

    #[Test]
    public function it_can_delete_a_category()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $category = Category::factory()->create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم حذف التصنيف بنجاح',
            ]);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_cannot_delete_a_category_that_has_products()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $category = Category::factory()->create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    #[Test]
    public function customers_cannot_create_categories()
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)
            ->postJson('/api/v1/admin/categories', [
                'name' => 'New Electronics'
            ])
            ->assertForbidden(); 
    }
    
    #[Test]
    public function admin_can_create_categories()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/categories', [
                'name' => 'New Electronics'
            ])
            ->assertCreated();
    }   
}