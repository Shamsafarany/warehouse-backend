<?php

namespace Tests\Feature;

use App\Domains\Identity\Domain\Enums\UserRole;
use App\Domains\Identity\Infrastructure\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

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
            ->patchJson('/api/v1/auth/password', [
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

    public function test_user_can_verify_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->actingAs($user, 'sanctum')
            ->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تفعيل البريد الإلكتروني بنجاح',
            ]);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_request_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إرسال رابط التفعيل إلى بريدك الإلكتروني',
            ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني.',
            ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        
        // Generate a valid password reset token using Laravel's Broker
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح.',
            ]);
    }

    public function test_invalid_login_returns_401(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('CorrectPassword123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword999!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status_code' => 401,
            ]);
    }

    public function test_unauthenticated_user_accessing_me_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status_code' => 401,
            ]);
    }

    public function test_wrong_current_password_on_password_update_returns_422(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123!'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/auth/password', [
                'current_password' => 'IncorrectOldPass!',
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status_code' => 422,
            ]);
    }

    public function test_unverified_user_hitting_verified_route_returns_403(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null, // Unverified
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/auth/profile', [
                'first_name' => 'Attempted Change',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status_code' => 403,
            ]);
    }

    public function test_rate_limit_exceeded_returns_429(): void
    {
        $user = User::factory()->create();

        // The login route has throttle:5,1
        // Hit it 6 times in rapid succession
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'WrongPassword',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'status_code' => 429,
            ]);
    }

    public function test_invalid_or_expired_reset_token_fails_appropriately(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-random-token-string',
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'status_code' => 400,
            ]);
    }
}