<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\PasswordPolicy;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('The MySQL PDO driver is not available in this environment.');
        }

        parent::setUp();

        $this->artisan('migrate:fresh');
    }

    public function test_user_can_login_refresh_and_manage_sessions(): void
    {
        $user = User::factory()->create([
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);

        $refreshResponse = $this->withToken($token)
            ->postJson('/api/auth/refresh');

        $refreshResponse->assertOk();
        $refreshedToken = $refreshResponse->json('token');

        $secondaryToken = $user->createToken('secondary-device');

        $sessionsResponse = $this->withToken($refreshedToken)
            ->getJson('/api/auth/sessions');

        $sessionsResponse->assertOk();
        $this->assertCount(2, $sessionsResponse->json('sessions'));

        $this->withToken($refreshedToken)
            ->deleteJson('/api/auth/sessions/'.$secondaryToken->accessToken->id)
            ->assertOk();

        $this->withToken($refreshedToken)
            ->deleteJson('/api/auth/sessions')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_can_setup_enable_and_verify_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        $token = $user->createToken('setup-device')->plainTextToken;

        $setupResponse = $this->withToken($token)
            ->postJson('/api/auth/2fa/setup');

        $setupResponse->assertOk()->assertJsonStructure(['secret', 'debug_code']);

        $this->withToken($token)
            ->postJson('/api/auth/2fa/enable', [
                'code' => $setupResponse->json('debug_code'),
            ])
            ->assertOk();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse->assertOk()->assertJsonPath('two_factor_required', true);

        $verifyResponse = $this->postJson('/api/auth/2fa/verify', [
            'challenge_token' => $loginResponse->json('challenge_token'),
            'code' => $loginResponse->json('debug_code'),
        ]);

        $verifyResponse->assertOk()->assertJsonStructure(['token', 'user']);

        $this->withToken($verifyResponse->json('token'))
            ->deleteJson('/api/auth/2fa')
            ->assertOk();
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

            $expectedStatus = $attempt === 4 ? 423 : 401;
            $response->assertStatus($expectedStatus);
        }

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(423);

        $this->assertNotNull($user->fresh()->lockout_until);
    }

    public function test_password_policy_rejects_weak_expired_and_reused_passwords(): void
    {
        $this->postJson('/api/auth/bootstrap-super-admin', [
            'name' => 'Weak Password Admin',
            'email' => 'weak@example.com',
            'password' => 'weakpass',
        ])->assertStatus(422);

        $expiredUser = User::factory()->create([
            'password_changed_at' => now()->subDays(91),
            'password_expires_at' => now()->subDay(),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $expiredUser->email,
            'password' => 'password',
        ])->assertStatus(423);

        $user = User::factory()->create();
        app(PasswordPolicy::class)->applyPassword($user, 'Password1!');

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertStatus(422);
    }

    public function test_roles_and_permissions_seed_expected_auth_matrix(): void
    {
        $this->seed();

        $this->assertDatabaseHas('roles', ['name' => 'receptionist']);
        $this->assertDatabaseHas('roles', ['name' => 'accountant']);
        $this->assertDatabaseHas('permissions', ['name' => 'auth.view-sessions']);
        $this->assertDatabaseHas('permissions', ['name' => 'billing.manage']);

        $superAdmin = Role::query()->where('name', 'super-admin')->firstOrFail();
        $this->assertTrue($superAdmin->permissions()->where('name', 'auth.revoke-sessions')->exists());
    }
}