<?php

namespace Tests\Feature;

use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Sham',
            'last_name' => 'Al Safarany',
            'email' => 'sham@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::CUSTOMER->value,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status_code' => 201,
                'message' => 'تم إنشاء الحساب بنجاح',
            ])
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'user' => ['id', 'first_name', 'last_name', 'email', 'role', 'created_at'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'sham@example.com',
            'first_name' => 'Sham',
            'last_name' => 'Al Safarany',
            'role' => UserRole::CUSTOMER->value,
        ]);
    }

    public function test_registration_fails_if_email_is_not_unique(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Another',
            'last_name' => 'User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::CUSTOMER->value,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status_code' => 422,
                'message' => 'فشل في التحقق من صحة البيانات المدخلة.',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم تسجيل الدخول بنجاح',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);

        // Verify the token was deleted from the database
        $this->assertCount(0, $user->tokens);
    }

    public function test_user_can_view_only_his_own_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Sham',
            'last_name' => 'Profile Test',
            'email' => 'profile@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'first_name' => 'Sham',
                    'last_name' => 'Profile Test',
                    'email' => 'profile@example.com',
                ],
            ]);
    }

    public function test_user_can_partially_update_only_his_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'OldFirst',
            'last_name' => 'OldLast',
            'email' => 'old@example.com',
        ]);

        // Sending only the first_name (partial update via PATCH)
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/auth/profile', [
                'first_name' => 'UpdatedFirst',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تحديث البيانات الشخصية بنجاح',
                'data' => [
                    'first_name' => 'UpdatedFirst',
                    'last_name' => 'OldLast', // Last name remains untouched
                    'email' => 'old@example.com', // Email remains untouched
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'UpdatedFirst',
            'last_name' => 'OldLast',
            'email' => 'old@example.com',
        ]);
    }

    public function test_user_can_update_his_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح',
            ]);

        // Verify password hash changed
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_user_can_delete_only_his_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم حذف الحساب بنجاح',
            ]);

        // Ensure user is completely removed from the database
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('auth_token')->plainTextToken;

        // Send request to refresh token using the old token
        $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken)
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'message' => 'تم تجديد الرمز بنجاح',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                ],
            ]);

        $newToken = $response->json('data.token');

        // Verify the old token is now deleted/revoked from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', explode('|', $oldToken)[1]),
        ]);

        // Verify the new token works for subsequent requests (e.g., hitting /me)
        $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);
    }
}