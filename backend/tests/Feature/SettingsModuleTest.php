<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SettingGeneral;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingsModuleTest extends TestCase
{
    private string $host = 'alpha.medcore.test';

    private Tenant $tenant;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('The MySQL PDO driver is not available in this environment.');
        }

        parent::setUp();

        $this->artisan('migrate:fresh');

        $this->seed(RolePermissionSeeder::class);
        $this->ensureRoleUserPivotTableExists();

        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });

        $this->tenant = Tenant::query()->create([
            'name' => 'Alpha Hospital',
            'subdomain' => 'alpha',
            'database_name' => 'alpha_hospital',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'settings-tester',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'settings.read',
            'settings.update',
            'settings.branding.update',
            'settings.notification.update',
            'settings.billing.update',
            'settings.clinical.update',
            'settings.integration.update',
            'settings.security.update',
            'settings.audit.read',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $permissionIds = Permission::query()
            ->whereIn('name', $permissions)
            ->pluck('id');

        $role->permissions()->syncWithoutDetaching($permissionIds);
        $this->user->roles()->syncWithoutDetaching([$role->id]);

        $this->token = $this->user->createToken('settings-tests')->plainTextToken;
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

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->tenantRequest('get', '/api/v1/settings/general', [], false);

        $response->assertUnauthorized();
    }

    public function test_missing_permission_returns_forbidden(): void
    {
        $userWithoutPermissions = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $token = $userWithoutPermissions->createToken('settings-tests-no-permission')->plainTextToken;

        $response = $this->tenantRequest('get', '/api/v1/settings/general', [], true, $token);

        $response->assertForbidden()
            ->assertJsonPath('message', 'Forbidden: missing permission.');
    }

    public function test_general_get_is_cached_and_put_clears_cache(): void
    {
        $seedResponse = $this->tenantRequest('put', '/api/v1/settings/general', $this->generalPayload('Initial Hospital'));
        $seedResponse->assertOk();

        $firstGet = $this->tenantRequest('get', '/api/v1/settings/general');
        $firstGet->assertOk();

        $firstHospitalName = $firstGet->json('data.settings.hospital_name');

        SettingGeneral::query()
            ->where('tenant_id', $this->tenant->id)
            ->update([
                'hospital_name' => 'Direct Database Change',
            ]);

        $secondGet = $this->tenantRequest('get', '/api/v1/settings/general');
        $secondGet->assertOk()
            ->assertJsonPath('data.settings.hospital_name', $firstHospitalName);

        $updatePayload = $this->generalPayload('API Updated Hospital');

        $putResponse = $this->tenantRequest('put', '/api/v1/settings/general', $updatePayload);
        $putResponse->assertOk()
            ->assertJsonPath('data.settings.hospital_name', 'API Updated Hospital');

        $thirdGet = $this->tenantRequest('get', '/api/v1/settings/general');
        $thirdGet->assertOk()
            ->assertJsonPath('data.settings.hospital_name', 'API Updated Hospital');
    }

    public function test_sensitive_fields_are_masked_in_get_endpoints(): void
    {
        $emailResponse = $this->tenantRequest('put', '/api/v1/settings/email-config', [
            'mail_driver' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_user' => 'mailer-user',
            'smtp_password' => 'super-secret-password',
            'smtp_encryption' => 'tls',
            'from_email' => 'noreply@example.com',
            'from_name' => 'HMS',
        ]);
        $emailResponse->assertOk();

        $emailGet = $this->tenantRequest('get', '/api/v1/settings/email-config');
        $emailGet->assertOk()
            ->assertJsonPath('data.settings.smtp_password', '********');

        $smsResponse = $this->tenantRequest('put', '/api/v1/settings/sms-config', [
            'provider_name' => 'twilio',
            'api_key' => 'test-api-key',
            'api_secret' => 'test-api-secret',
            'sender_id' => 'HMS',
            'base_url' => 'https://api.twilio.com',
        ]);
        $smsResponse->assertOk();

        $smsGet = $this->tenantRequest('get', '/api/v1/settings/sms-config');
        $smsGet->assertOk()
            ->assertJsonPath('data.settings.api_key', '********')
            ->assertJsonPath('data.settings.api_secret', '********');
    }

    public function test_email_config_test_endpoint_success_and_validation_error(): void
    {
        $this->tenantRequest('put', '/api/v1/settings/email-config', [
            'mail_driver' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_user' => 'mailer-user',
            'smtp_password' => 'super-secret-password',
            'smtp_encryption' => 'tls',
            'from_email' => 'noreply@example.com',
            'from_name' => 'HMS',
        ])->assertOk();

        Mail::fake();

        $success = $this->tenantRequest('post', '/api/v1/settings/email-config/test', [
            'recipient_email' => 'test@recipient.com',
        ]);

        $success->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.test_result', 'passed');

        $validationError = $this->tenantRequest('post', '/api/v1/settings/email-config/test', []);

        $validationError->assertStatus(422);
    }

    public function test_sms_config_test_endpoint_error_and_success(): void
    {
        $error = $this->tenantRequest('post', '/api/v1/settings/sms-config/test', [
            'recipient_phone' => '8801700000000',
        ]);

        $error->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data.test_result', 'failed');

        $this->tenantRequest('put', '/api/v1/settings/sms-config', [
            'provider_name' => 'twilio',
            'api_key' => 'test-api-key',
            'api_secret' => 'test-api-secret',
            'sender_id' => 'HMS',
            'base_url' => 'https://api.twilio.com',
        ])->assertOk();

        $success = $this->tenantRequest('post', '/api/v1/settings/sms-config/test', [
            'recipient_phone' => '8801700000000',
        ]);

        $success->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.test_result', 'passed');
    }

    public function test_audit_logs_support_pagination_and_section_filter(): void
    {
        $this->tenantRequest('put', '/api/v1/settings/general', $this->generalPayload('Audit General Update'))
            ->assertOk();

        $this->tenantRequest('put', '/api/v1/settings/billing', [
            'invoice_prefix' => 'INV',
            'receipt_prefix' => 'REC',
            'estimate_prefix' => 'EST',
            'refund_prefix' => 'REF',
            'tax_name' => 'VAT',
            'tax_percentage' => 5,
            'invoice_footer' => 'Thank you',
            'auto_generate_invoice_number' => true,
            'allow_manual_discount' => true,
            'require_discount_approval' => false,
        ])->assertOk();

        $paginated = $this->tenantRequest('get', '/api/v1/settings/audit-logs?per_page=1');
        $paginated->assertOk()
            ->assertJsonPath('pagination.per_page', 1);

        $this->assertGreaterThanOrEqual(2, (int) $paginated->json('pagination.total'));
        $this->assertIsArray($paginated->json('data'));

        $filtered = $this->tenantRequest('get', '/api/v1/settings/audit-logs?section=general&per_page=10');
        $filtered->assertOk();

        $records = $filtered->json('data');
        $this->assertIsArray($records);

        foreach ($records as $record) {
            $this->assertSame('general', $record['section']);
        }
    }

    private function generalPayload(string $hospitalName): array
    {
        return [
            'hospital_name' => $hospitalName,
            'hospital_code' => 'AH001',
            'registration_no' => 'REG-001',
            'license_no' => 'LIC-001',
            'email' => 'info@alpha.test',
            'phone' => '01710000000',
            'emergency_phone' => '01719999999',
            'website' => 'https://alpha.test',
            'address_line_1' => '123 Main Road',
            'address_line_2' => 'Floor 2',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'country' => 'Bangladesh',
            'zip_code' => '1205',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'USD',
            'language' => 'en',
            'date_format' => 'YYYY-MM-DD',
            'time_format' => 'HH:mm:ss',
            'logo_url' => 'https://cdn.alpha.test/logo.png',
            'favicon_url' => 'https://cdn.alpha.test/favicon.png',
        ];
    }

    private function tenantRequest(string $method, string $uri, array $data = [], bool $authenticate = true, ?string $token = null)
    {
        $url = "http://{$this->host}{$uri}";

        $request = $this->withServerVariables([
            'HTTP_HOST' => $this->host,
            'SERVER_NAME' => $this->host,
        ])
            ->withHeader('Host', $this->host);

        if ($authenticate) {
            $request = $request->withToken($token ?? $this->token);
        }

        return match ($method) {
            'post' => $request->postJson($url, $data),
            'get' => $request->getJson($url),
            'put' => $request->putJson($url, $data),
            default => throw new \InvalidArgumentException('Unsupported request method.'),
        };
    }
}
