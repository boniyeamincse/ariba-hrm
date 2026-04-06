<?php

namespace App\Services\Settings;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Settings\SettingRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class SettingService
{
    public const SECTIONS = [
        'general',
        'branding',
        'localization',
        'notifications',
        'email-config',
        'sms-config',
        'security',
        'billing',
        'clinical',
        'integrations',
        'audit-logs',
    ];

    private const SECTION_SECRET_KEYS = [
        'email-config' => ['password', 'smtp_password', 'api_key', 'api_secret'],
        'sms-config' => ['password', 'token', 'api_key', 'api_secret', 'access_token'],
        'security' => ['jwt_secret', 'sso_client_secret', 'private_key'],
        'billing' => ['api_key', 'api_secret', 'webhook_secret', 'secret_key'],
        'integrations' => ['api_key', 'api_secret', 'client_secret', 'token', 'access_token', 'refresh_token', 'webhook_secret'],
    ];

    public function __construct(private readonly SettingRepository $repository)
    {
    }

    public function resolveTenantId(?int $tenantIdFromContext, ?User $user, ?int $tenantIdOverride = null): int
    {
        if ($tenantIdFromContext) {
            return $tenantIdFromContext;
        }

        if ($tenantIdOverride && $user?->hasRole('super-admin')) {
            $exists = Tenant::query()->whereKey($tenantIdOverride)->exists();
            if (! $exists) {
                throw ValidationException::withMessages([
                    'tenant_id' => 'The selected tenant does not exist.',
                ]);
            }

            return $tenantIdOverride;
        }

        if ($user?->tenant_id) {
            return (int) $user->tenant_id;
        }

        throw ValidationException::withMessages([
            'tenant_id' => 'Unable to resolve tenant context. Use tenant subdomain or provide tenant_id as super-admin.',
        ]);
    }

    public function list(int $tenantId): array
    {
        $existing = $this->repository->listByTenant($tenantId);
        $result = [];

        foreach (self::SECTIONS as $section) {
            if ($section === 'audit-logs') {
                $result[] = $this->auditLogsSection($tenantId);
                continue;
            }

            $setting = $existing->get($section);

            if (! $setting) {
                $result[] = [
                    'section' => $section,
                    'data' => [],
                    'updated_at' => null,
                    'updated_by' => null,
                ];
                continue;
            }

            $decrypted = $this->decryptData($setting->data ?? [], $setting->encrypted_keys ?? []);
            $masked = $this->maskData($decrypted, $setting->encrypted_keys ?? []);

            $result[] = [
                'section' => $section,
                'data' => $masked,
                'updated_at' => optional($setting->updated_at)?->toISOString(),
                'updated_by' => $setting->updated_by,
            ];
        }

        return $result;
    }

    public function show(int $tenantId, string $section): array
    {
        $this->assertSection($section);

        if ($section === 'audit-logs') {
            return $this->auditLogsSection($tenantId);
        }

        $setting = $this->repository->findByTenantAndSection($tenantId, $section);

        if (! $setting) {
            return [
                'section' => $section,
                'data' => [],
                'updated_at' => null,
                'updated_by' => null,
            ];
        }

        $decrypted = $this->decryptData($setting->data ?? [], $setting->encrypted_keys ?? []);

        return [
            'section' => $section,
            'data' => $this->maskData($decrypted, $setting->encrypted_keys ?? []),
            'updated_at' => optional($setting->updated_at)?->toISOString(),
            'updated_by' => $setting->updated_by,
        ];
    }

    public function update(int $tenantId, string $section, array $data, User $user, string $path, string $ip): array
    {
        $this->assertSection($section);

        if ($section === 'audit-logs') {
            throw ValidationException::withMessages([
                'section' => 'The audit-logs section is read-only.',
            ]);
        }

        [$encryptedData, $encryptedKeys] = $this->encryptData($section, $data);

        $setting = $this->repository->upsert(
            tenantId: $tenantId,
            section: $section,
            data: $encryptedData,
            encryptedKeys: $encryptedKeys,
            updatedBy: $user->id,
        );

        AuditLog::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenantId,
            'method' => 'PATCH',
            'path' => $path,
            'status_code' => 200,
            'ip_address' => $ip,
            'payload' => [
                'action' => 'settings.updated',
                'section' => $section,
                'updated_keys' => array_keys(Arr::dot($data)),
            ],
        ]);

        $decrypted = $this->decryptData($setting->data ?? [], $setting->encrypted_keys ?? []);

        return [
            'section' => $section,
            'data' => $this->maskData($decrypted, $setting->encrypted_keys ?? []),
            'updated_at' => optional($setting->updated_at)?->toISOString(),
            'updated_by' => $setting->updated_by,
        ];
    }

    private function assertSection(string $section): void
    {
        if (! in_array($section, self::SECTIONS, true)) {
            throw ValidationException::withMessages([
                'section' => 'Unsupported section. Allowed: '.implode(', ', self::SECTIONS),
            ]);
        }
    }

    private function encryptData(string $section, array $data): array
    {
        $secretKeys = self::SECTION_SECRET_KEYS[$section] ?? [];
        $encryptedKeys = [];

        $walker = function ($node, string $path = '') use (&$walker, $secretKeys, &$encryptedKeys) {
            if (! is_array($node)) {
                return $node;
            }

            $result = [];

            foreach ($node as $key => $value) {
                $nextPath = $path === '' ? (string) $key : $path.'.'.$key;

                if (is_array($value)) {
                    $result[$key] = $walker($value, $nextPath);
                    continue;
                }

                if (in_array((string) $key, $secretKeys, true) && $value !== null && $value !== '') {
                    $result[$key] = Crypt::encryptString((string) $value);
                    $encryptedKeys[] = $nextPath;
                } else {
                    $result[$key] = $value;
                }
            }

            return $result;
        };

        return [$walker($data), $encryptedKeys];
    }

    private function decryptData(array $data, array $encryptedKeys): array
    {
        foreach ($encryptedKeys as $path) {
            $current = data_get($data, $path);
            if (! is_string($current) || $current === '') {
                continue;
            }

            try {
                data_set($data, $path, Crypt::decryptString($current));
            } catch (\Throwable) {
                data_set($data, $path, null);
            }
        }

        return $data;
    }

    private function maskData(array $data, array $encryptedKeys): array
    {
        foreach ($encryptedKeys as $path) {
            $current = data_get($data, $path);
            if (! is_string($current) || $current === '') {
                continue;
            }

            data_set($data, $path, $this->maskValue($current));
        }

        return $data;
    }

    private function maskValue(string $value): string
    {
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }

        return str_repeat('*', strlen($value) - 4).substr($value, -4);
    }

    private function auditLogsSection(int $tenantId): array
    {
        $logs = AuditLog::query()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->limit(20)
            ->get(['id', 'method', 'path', 'status_code', 'ip_address', 'created_at']);

        return [
            'section' => 'audit-logs',
            'data' => [
                'items' => $logs,
            ],
            'updated_at' => optional($logs->first()?->created_at)?->toISOString(),
            'updated_by' => null,
        ];
    }
}
