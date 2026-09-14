<?php

namespace Tests\Feature;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_paginated_categories()
    {
        Category::create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $response = $this->getJson('/api/admin/v1/categories');

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

        $response = $this->postJson('/api/admin/v1/categories', $payload);

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
        $response = $this->postJson('/api/admin/v1/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                'name' => ['حقل اسم التصنيف إجباري.']
            ]);
    }

    #[Test]
    public function it_can_show_a_single_category()
    {
        $category = Category::create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $response = $this->getJson("/api/admin/v1/categories/{$category->id}");

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
        $category = Category::create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $payload = [
            'name' => 'الاسم المحدث',
            'description' => 'وصف جديد'
        ];

        $response = $this->putJson("/api/admin/v1/categories/{$category->id}", $payload);

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
        $category = Category::create([
            'name' => 'لفائف الصلب',
            'description' => 'وصف تجريبي'
        ]);

        $response = $this->deleteJson("/api/admin/v1/categories/{$category->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم حذف التصنيف بنجاح',
            ]);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}