<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

class TenantManagementApiTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('The MySQL PDO driver is not available in this environment.');
        }

        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(90),
        ]);

        $superAdminRole = Role::query()->where('name', 'super-admin')->firstOrFail();
        $user->roles()->syncWithoutDetaching([$superAdminRole->id]);

        $this->token = $user->createToken('tenant-management-tests')->plainTextToken;
    }

    public function test_super_admin_can_create_view_update_and_archive_tenant(): void
    {
        $createResponse = $this->withToken($this->token)->postJson('/api/tenants', [
            'name' => 'Blue River Hospital',
            'subdomain' => 'blue-river',
            'database_name' => 'tenant_blue_river',
            'admin_name' => 'Blue River Admin',
            'admin_email' => 'admin@blue-river.test',
            'admin_password' => 'Password1!',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('tenant.name', 'Blue River Hospital')
            ->assertJsonPath('tenant.status', 'active');

        $tenantId = (int) $createResponse->json('tenant.id');

        $this->withToken($this->token)
            ->getJson('/api/tenants/'.$tenantId)
            ->assertOk()
            ->assertJsonPath('tenant.id', $tenantId)
            ->assertJsonPath('summary.users_total', 1);

        $this->withToken($this->token)
            ->patchJson('/api/tenants/'.$tenantId, [
                'name' => 'Blue River Medical Center',
                'subdomain' => 'blue-river-mc',
            ])
            ->assertOk()
            ->assertJsonPath('tenant.name', 'Blue River Medical Center')
            ->assertJsonPath('tenant.subdomain', 'blue-river-mc');

        $this->withToken($this->token)
            ->patchJson('/api/tenants/'.$tenantId.'/status', ['status' => 'suspended'])
            ->assertOk()
            ->assertJsonPath('tenant.status', 'suspended');

        $this->withToken($this->token)
            ->deleteJson('/api/tenants/'.$tenantId, ['mode' => 'archive'])
            ->assertOk()
            ->assertJsonPath('tenant.status', 'archived');
    }

    public function test_super_admin_can_permanently_delete_tenant_without_users(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Delete Me Hospital',
            'subdomain' => 'delete-me',
            'database_name' => 'tenant_delete_me',
            'status' => 'active',
        ]);

        $this->withToken($this->token)
            ->deleteJson('/api/tenants/'.$tenant->id, ['mode' => 'delete'])
            ->assertOk();

        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    public function test_super_admin_cannot_delete_tenant_with_users(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Protected Tenant',
            'subdomain' => 'protected-tenant',
            'database_name' => 'tenant_protected',
            'status' => 'active',
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'staff@protected-tenant.test',
        ]);

        $this->withToken($this->token)
            ->deleteJson('/api/tenants/'.$tenant->id, ['mode' => 'delete'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cannot permanently delete tenant while users still exist. Archive instead.');
    }

    public function test_non_super_admin_cannot_access_tenant_management_endpoints(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('basic-user')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/tenants')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Forbidden: missing permission.');
    }
}
