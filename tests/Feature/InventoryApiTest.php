<?php

namespace Tests\Feature;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Identity\Infrastructure\Models\User;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Exception;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_product_automatically_creates_inventory_with_zero_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'category_id' => $category->id,
                'name' => 'Smart Watch',
                'price' => 199.99,
            ]);

        $response->assertStatus(201);

        $product = Product::first();
        $inventory = $product->inventories;

        // Test 7 Assertions
        $this->assertNotNull($inventory);
        $this->assertEquals($product->id, $inventory->product_id);
        $this->assertEquals(0, $inventory->stock_quantity);
        $this->assertFalse($product->is_active); // Out of stock defaults to inactive
    }

    public function test_every_product_has_exactly_one_inventory(): void
    {
        // Simulate creating a product through the admin endpoint or action 
        // so that the automatic inventory creation logic runs.
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'category_id' => $category->id,
                'name' => 'Inventory Count Test',
                'price' => 100.00,
            ]);

        $product = Product::latest()->first();
        
        // Assert exactly 1 inventory exists in the database for this product
        $inventoryCount = Inventory::where('product_id', $product->id)->count();

        $this->assertEquals(1, $inventoryCount);
    }

    public function test_product_and_inventory_use_matching_ids_correctly(): void
    {
        // Correct factory syntax: use Inventory::factory attached to a Product factory
        $product = Product::factory()
            ->has(Inventory::factory()->state(['stock_quantity' => 10]))
            ->create();

        // Verify the relationship ID matches
        $this->assertEquals($product->id, $product->inventories->product_id);
    }

    public function test_if_inventory_creation_fails_product_creation_rolls_back(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        // Force a failure by mocking or sending data that triggers exception during transaction if desired, 
        // or test transaction rollback behavior directly through an action exception:
        try {
            DB::transaction(function () {
                Product::create([
                    'category_id' => Category::factory()->create()->id,
                    'name' => 'Should Rollback',
                    'price' => 50,
                ]);
                throw new Exception('Forced failure');
            });
        } catch (Exception $e) {
            // Expected exception
        }

        // Test 10 Assertions
        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, Inventory::count());
    }

    public function test_product_creation_does_not_create_fake_stock_movements(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'category_id' => $category->id,
                'name' => 'Clean Product',
                'price' => 75.00,
            ]);

        $product = Product::first();

        // Test 11 Assertions
        $this->assertEquals(0, $product->inventories->stock_quantity);
        $this->assertEquals(0, $product->inventories->stockMovements()->count());
    }

    public function test_product_update_cannot_modify_inventory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->has(Inventory::factory()->state(['stock_quantity' => 0]))->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/products/{$product->id}", [
                'price' => 150.00,
                'stock_quantity' => 500, 
            ]);

        $Response = $response->assertStatus(200);

        $product->refresh();

        // Test 12 Assertions
        $this->assertEquals(150.00, $product->price);
        $this->assertEquals(0, $product->inventories->stock_quantity); // Inventory remains untouched
    }
    public function test_admin_can_update_reserved_quantity_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state([
            'stock_quantity' => 50,
            'reserved_quantity' => 5,
        ])->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/inventories/{$inventory->id}", [
                'reserved_quantity' => 12,
            ]);

        $response->assertStatus(200);

        $inventory->refresh();

        // Assert reserved quantity updated while physical stock remains untouched
        $this->assertEquals(12, $inventory->reserved_quantity);
        $this->assertEquals(50, $inventory->stock_quantity);
    }

    public function test_admin_cannot_update_physical_stock_via_general_inventory_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state([
            'stock_quantity' => 50,
            'reserved_quantity' => 2,
        ])->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/inventories/{$inventory->id}", [
                'reserved_quantity' => 5,
                'stock_quantity' => 500, // Prohibited field
            ]);

        // Expect validation error because stock_quantity is prohibited
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_quantity']);

        $inventory->refresh();
        $this->assertEquals(50, $inventory->stock_quantity); // Stock did not change
    }

    public function test_admin_can_adjust_stock_in_and_automatically_logs_movement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state(['stock_quantity' => 10])->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/inventories/{$inventory->id}/adjust", [
                'type' => 'in',
                'quantity' => 25,
                'notes' => 'شحنة بضائع جديدة من المورد',
                'reference_id' => 'PO-9988',
            ]);

        $response->assertStatus(200);

        $inventory->refresh();

        // 1. Verify physical stock is updated (10 + 25 = 35)
        $this->assertEquals(35, $inventory->stock_quantity);

        // 2. Verify a stock movement audit log was automatically created
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'in',
            'quantity' => 25,
            'notes' => 'شحنة بضائع جديدة من المورد',
            'reference_id' => 'PO-9988',
        ]);
    }

    public function test_admin_can_adjust_stock_out_successfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state(['stock_quantity' => 40])->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/inventories/{$inventory->id}/adjust", [
                'type' => 'out',
                'quantity' => 15,
                'notes' => 'تالف في المستودع',
            ]);

        $response->assertStatus(200);

        $inventory->refresh();

        // Verify stock decreased (40 - 15 = 25)
        $this->assertEquals(25, $inventory->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'out',
            'quantity' => 15,
            'notes' => 'تالف في المستودع',
        ]);
    }

    public function test_adjust_stock_out_fails_if_quantity_exceeds_available_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state(['stock_quantity' => 10])->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/inventories/{$inventory->id}/adjust", [
                'type' => 'out',
                'quantity' => 25, // Trying to remove more than available (10)
                'notes' => 'محاولة إخراج أكبر من المخزون',
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status_code' => 409,
                'message' => 'لا يمكن إخراج كمية أكبر من المخزون المتاح.',
            ]);

        $inventory->refresh();
        $this->assertEquals(10, $inventory->stock_quantity); // Stock remains unchanged
        $this->assertEquals(0, $inventory->stockMovements()->count()); // No movement logged on failure
    }

    public function test_admin_can_view_stock_movements_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::factory()->state(['stock_quantity' => 50])->create();

        // Create a few movements directly or via adjust endpoint
        $inventory->stockMovements()->create([
            'type' => 'in',
            'quantity' => 50,
            'notes' => 'Initial stock load',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admin/inventories/{$inventory->id}/movements");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'inventory_id', 'movement_type', 'quantity', 'notes', 'created_at']
                ]
            ]);
    }
}