<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Facility;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffModuleTest extends TestCase
{
    use RefreshDatabase;

    private string $alphaHost = 'alpha.medcore.test';

    private string $betaHost = 'beta.medcore.test';

    private Tenant $alphaTenant;

    private Tenant $betaTenant;

    private User $alphaUser;

    private User $betaUser;

    private string $alphaToken;

    private string $betaToken;

    private array $staffPermissions = [
        'staff.create',
        'staff.view',
        'staff.update',
        'staff.delete',
        'staff.status.update',
        'staff.assign.branch',
        'staff.assign.facility',
        'staff.assign.department',
        'staff.assign.manager',
        'staff.assign.user',
        'staff.employment-history.view',
        'staff.license.manage',
        'staff.emergency-contact.manage',
        'staff.document.manage',
    ];

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('The MySQL PDO driver is not available in this environment.');
        }

        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->ensureRoleUserPivotTableExists();

        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });

        $this->alphaTenant = Tenant::query()->create([
            'name' => 'Alpha Hospital',
            'subdomain' => 'alpha',
            'database_name' => 'alpha_hospital',
            'status' => 'active',
        ]);

        $this->betaTenant = Tenant::query()->create([
            'name' => 'Beta Hospital',
            'subdomain' => 'beta',
            'database_name' => 'beta_hospital',
            'status' => 'active',
        ]);

        $this->alphaUser = User::factory()->create([
            'tenant_id' => $this->alphaTenant->id,
        ]);

        $this->betaUser = User::factory()->create([
            'tenant_id' => $this->betaTenant->id,
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'staff-tester',
            'guard_name' => 'web',
        ]);

        foreach ($this->staffPermissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $permissionIds = Permission::query()
            ->whereIn('name', $this->staffPermissions)
            ->pluck('id');

        $role->permissions()->syncWithoutDetaching($permissionIds);
        $this->alphaUser->roles()->syncWithoutDetaching([$role->id]);
        $this->betaUser->roles()->syncWithoutDetaching([$role->id]);

        $this->alphaToken = $this->alphaUser->createToken('staff-alpha-tests')->plainTextToken;
        $this->betaToken = $this->betaUser->createToken('staff-beta-tests')->plainTextToken;
    }

    public function test_unauthenticated_staff_request_is_rejected(): void
    {
        $response = $this->tenantRequest('get', $this->alphaHost, '/api/v1/staff', [], false);

        $response->assertUnauthorized();
    }

    public function test_missing_staff_permission_returns_forbidden(): void
    {
        $userWithoutPermissions = User::factory()->create([
            'tenant_id' => $this->alphaTenant->id,
        ]);

        $token = $userWithoutPermissions->createToken('staff-no-permission')->plainTextToken;

        $response = $this->tenantRequest('get', $this->alphaHost, '/api/v1/staff', [], true, $token);

        $response->assertForbidden()
            ->assertJsonPath('message', 'Forbidden: missing permission.');
    }

    public function test_create_staff_records_history_and_audit_log(): void
    {
        $branch = $this->createBranch($this->alphaTenant, $this->alphaUser, 'alpha-main');
        $facility = $this->createFacility($this->alphaTenant, $branch, $this->alphaUser, 'alpha-fac');

        $response = $this->tenantRequest('post', $this->alphaHost, '/api/v1/staff', [
            'employee_code' => 'EMP-ALPHA-001',
            'first_name' => 'Amin',
            'last_name' => 'Rahman',
            'join_date' => now()->toDateString(),
            'status' => Staff::STATUS_PROBATION,
            'branch_id' => $branch->id,
            'facility_id' => $facility->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.employee_code', 'EMP-ALPHA-001')
            ->assertJsonPath('data.tenant_id', $this->alphaTenant->id);

        $staffId = (int) $response->json('data.id');

        $this->assertDatabaseHas('staff', [
            'id' => $staffId,
            'tenant_id' => $this->alphaTenant->id,
            'employee_code' => 'EMP-ALPHA-001',
        ]);

        $this->assertDatabaseHas('staff_employment_histories', [
            'tenant_id' => $this->alphaTenant->id,
            'staff_id' => $staffId,
            'action' => 'staff_created',
            'new_status' => Staff::STATUS_PROBATION,
        ]);

        $this->assertDatabaseHas('staff_audit_logs', [
            'tenant_id' => $this->alphaTenant->id,
            'target_type' => 'staff',
            'target_id' => $staffId,
            'action' => 'staff_created',
        ]);
    }

    public function test_staff_details_are_tenant_isolated(): void
    {
        $branch = $this->createBranch($this->alphaTenant, $this->alphaUser, 'iso-main');

        $staff = $this->createStaff($this->alphaTenant, $this->alphaUser, [
            'employee_code' => 'EMP-ISO-001',
            'branch_id' => $branch->id,
        ]);

        $response = $this->tenantRequest(
            'get',
            $this->betaHost,
            '/api/v1/staff/'.$staff->id,
            [],
            true,
            $this->betaToken
        );

        $response->assertNotFound();
    }

    public function test_invalid_lifecycle_transition_is_rejected(): void
    {
        $branch = $this->createBranch($this->alphaTenant, $this->alphaUser, 'life-main');

        $staff = $this->createStaff($this->alphaTenant, $this->alphaUser, [
            'employee_code' => 'EMP-LIFE-001',
            'branch_id' => $branch->id,
            'status' => Staff::STATUS_PROBATION,
        ]);

        $confirm = $this->tenantRequest('post', $this->alphaHost, '/api/v1/staff/'.$staff->id.'/confirm', [
            'remarks' => 'Probation successfully completed',
        ]);

        $confirm->assertOk()
            ->assertJsonPath('data.status', Staff::STATUS_ACTIVE);

        $invalid = $this->tenantRequest('post', $this->alphaHost, '/api/v1/staff/'.$staff->id.'/probation', [
            'remarks' => 'Trying invalid reverse transition',
        ]);

        $invalid->assertStatus(422)
            ->assertJsonFragment(['status' => "Invalid status transition from 'active' to 'probation'."]);
    }

    public function test_assign_facility_rejects_cross_tenant_facility_id(): void
    {
        $alphaBranch = $this->createBranch($this->alphaTenant, $this->alphaUser, 'alpha-assign-main');
        $staff = $this->createStaff($this->alphaTenant, $this->alphaUser, [
            'employee_code' => 'EMP-ASSIGN-001',
            'branch_id' => $alphaBranch->id,
        ]);

        $betaBranch = $this->createBranch($this->betaTenant, $this->betaUser, 'beta-assign-main');
        $betaFacility = $this->createFacility($this->betaTenant, $betaBranch, $this->betaUser, 'beta-assign-fac');

        $response = $this->tenantRequest('post', $this->alphaHost, '/api/v1/staff/'.$staff->id.'/assign-facility', [
            'facility_id' => $betaFacility->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('facility_id');

        $staff->refresh();
        $this->assertNull($staff->facility_id);
    }

    private function createBranch(Tenant $tenant, User $actor, string $slug): Branch
    {
        return Branch::query()->create([
            'tenant_id' => $tenant->id,
            'code' => strtoupper(substr($slug, 0, 8)).'-'.strtoupper(substr(md5($slug), 0, 3)),
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'address_line_1' => '123 Main Road',
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'BDT',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function createFacility(Tenant $tenant, Branch $branch, User $actor, string $slug): Facility
    {
        return Facility::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'code' => strtoupper(substr($slug, 0, 8)).'-'.strtoupper(substr(md5($slug), 0, 3)),
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function createStaff(Tenant $tenant, User $actor, array $overrides = []): Staff
    {
        return Staff::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'employee_code' => 'EMP-'.strtoupper(substr(md5((string) microtime(true)), 0, 8)),
            'first_name' => 'Test',
            'last_name' => 'Staff',
            'join_date' => now()->toDateString(),
            'status' => Staff::STATUS_PROBATION,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ], $overrides));
    }

    private function ensureRoleUserPivotTableExists(): void
    {
        if (Schema::hasTable('role_user')) {
            return;
        }

        Schema::create('role_user', static function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['role_id', 'user_id']);
        });
    }

    private function tenantRequest(
        string $method,
        string $host,
        string $uri,
        array $data = [],
        bool $authenticate = true,
        ?string $token = null
    ) {
        $url = "http://{$host}{$uri}";

        $request = $this->withServerVariables([
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
        ])->withHeader('Host', $host);

        if ($authenticate) {
            $request = $request->withToken($token ?? $this->alphaToken);
        }

        return match (strtolower($method)) {
            'post' => $request->postJson($url, $data),
            'get' => $request->getJson($url),
            'put' => $request->putJson($url, $data),
            'patch' => $request->patchJson($url, $data),
            'delete' => $request->deleteJson($url, $data),
            default => throw new \InvalidArgumentException('Unsupported request method.'),
        };
    }
}
