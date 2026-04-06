<?php

namespace App\Repositories\Settings;

use App\Models\TenantSetting;
use Illuminate\Support\Collection;

class SettingRepository
{
    public function findByTenantAndSection(int $tenantId, string $section): ?TenantSetting
    {
        return TenantSetting::query()
            ->where('tenant_id', $tenantId)
            ->where('section', $section)
            ->first();
    }

    public function listByTenant(int $tenantId): Collection
    {
        return TenantSetting::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('section');
    }

    public function upsert(
        int $tenantId,
        string $section,
        array $data,
        array $encryptedKeys,
        ?int $updatedBy
    ): TenantSetting {
        return TenantSetting::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'section' => $section,
            ],
            [
                'data' => $data,
                'encrypted_keys' => $encryptedKeys,
                'updated_by' => $updatedBy,
            ]
        );
    }
}
