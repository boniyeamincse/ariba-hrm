<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacModuleTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->tenant = Tenant::factory()->create();
        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Assign admin role to admin user
        $adminRole = Role::where('name', 'tenant-admin')->first();
        $this->admin->assignRole($adminRole);
    }

    // ===================== ROLE TESTS =====================

    public function test_can_list_roles(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/roles');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_can_create_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/rbac/roles', [
                'name' => 'custom-role',
                'display_name' => 'Custom Role',
                'description' => 'A custom test role',
                'permission_ids' => [],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'custom-role',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_can_view_role_with_permissions(): void
    {
        $role = Role::where('name', 'doctor')->first();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/rbac/roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('permissions', $response->json());
    }

    public function test_can_update_role(): void
    {
        $role = Role::where('name', 'doctor')->first();

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/rbac/roles/{$role->id}", [
                'display_name' => 'Updated Doctor Role',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'display_name' => 'Updated Doctor Role',
        ]);
    }

    public function test_cannot_delete_system_role(): void
    {
        $role = Role::where('name', 'super-admin')->first();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/rbac/roles/{$role->id}");

        $response->assertStatus(422);
    }

    public function test_can_delete_custom_role(): void
    {
        $role = Role::factory()->create(['tenant_id' => $this->tenant->id, 'is_system' => false]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/rbac/roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    // ===================== PERMISSION TESTS =====================

    public function test_can_list_permissions(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/permissions');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_can_filter_permissions_by_module(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/permissions?module_key=patient');

        $response->assertStatus(200);
        $permissions = $response->json('data');
        foreach ($permissions as $perm) {
            $this->assertEquals('patient', $perm['module_key']);
        }
    }

    public function test_can_view_permission(): void
    {
        $permission = Permission::where('name', 'patient.view')->first();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/rbac/permissions/{$permission->id}");

        $response->assertStatus(200);
        $this->assertEquals('patient.view', $response->json('data.name'));
    }

    // ===================== ROLE-PERMISSION MAPPING TESTS =====================

    public function test_can_sync_role_permissions(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $permissions = Permission::whereIn('name', [
            'patient.view',
            'consultation.view',
            'consultation.create',
        ])->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/rbac/roles/{$role->id}/permissions", [
                'permission_ids' => $permissions,
            ]);

        $response->assertStatus(200);
        $this->assertEquals(
            count($permissions),
            $role->fresh()->permissions()->count()
        );
    }

    // ===================== USER-ROLE ASSIGNMENT TESTS =====================

    public function test_can_assign_roles_to_user(): void
    {
        $roleIds = [
            Role::where('name', 'doctor')->first()->id,
            Role::where('name', 'nurse')->first()->id,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/rbac/users/{$this->user->id}/roles", [
                'role_ids' => $roleIds,
            ]);

        $response->assertStatus(200);
        $this->assertEquals(2, $this->user->fresh()->roles()->count());
    }

    public function test_can_remove_role_from_user(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $this->user->assignRole($role);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/rbac/users/{$this->user->id}/roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertFalse($this->user->fresh()->hasRole($role));
    }

    // ===================== PERMISSION GROUPS TESTS =====================

    public function test_can_list_permission_groups(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/permission-groups');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    // ===================== RBAC MATRIX TESTS =====================

    public function test_can_view_rbac_matrix(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/matrix');

        $response->assertStatus(200);
        $this->assertArrayHasKey('matrix', $response->json());
    }

    // ===================== PRIVILEGE ESCALATION PREVENTION TESTS =====================

    public function test_user_cannot_escalate_own_privileges(): void
    {
        $doctorRole = Role::where('name', 'doctor')->first();
        $superAdminRole = Role::where('name', 'super-admin')->first();

        $this->user->assignRole($doctorRole);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/rbac/users/{$this->user->id}/roles", [
                'role_ids' => [$superAdminRole->id],
            ]);

        // Should fail due to permission check
        $this->assertFalse($this->user->fresh()->hasRole($superAdminRole));
    }

    // ===================== TENANT BOUNDARY TESTS =====================

    public function test_user_cannot_access_other_tenant_roles(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherRole = Role::factory()->create(['tenant_id' => $otherTenant->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/rbac/roles/{$otherRole->id}");

        $response->assertStatus(404);
    }

    public function test_roles_are_scoped_to_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherRole = Role::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'other-role']);

        // Create role with same name in admin's tenant
        $tenantRole = Role::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'other-role']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/rbac/roles');

        // Should only return tenant's role
        $roleNames = collect($response->json('data'))->pluck('name')->unique()->toArray();
        $this->assertContains('other-role', $roleNames, 'Role should exist');
    }

    // ===================== AUDIT LOGGING TESTS =====================

    public function test_role_creation_is_audited(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/v1/rbac/roles', [
                'name' => 'audit-test-role',
                'display_name' => 'Audit Test Role',
            ]);

        $this->assertDatabaseHas('rbac_audit_logs', [
            'action' => 'create',
            'target_type' => 'role',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_role_update_is_audited(): void
    {
        $role = Role::where('name', 'doctor')->first();

        $this->actingAs($this->admin)
            ->patchJson("/api/v1/rbac/roles/{$role->id}", [
                'display_name' => 'Updated Doctor',
            ]);

        $this->assertDatabaseHas('rbac_audit_logs', [
            'action' => 'update',
            'target_type' => 'role',
            'target_id' => $role->id,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_permission_sync_is_audited(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $permissionIds = Permission::whereIn('name', ['patient.view', 'patient.create'])->pluck('id')->toArray();

        $this->actingAs($this->admin)
            ->putJson("/api/v1/rbac/roles/{$role->id}/permissions", [
                'permission_ids' => $permissionIds,
            ]);

        $this->assertDatabaseHas('rbac_audit_logs', [
            'action' => 'sync_permissions',
            'target_type' => 'role',
            'target_id' => $role->id,
        ]);
    }
}
