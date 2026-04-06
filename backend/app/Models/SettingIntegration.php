<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingIntegration extends Model
{
    protected $table = 'setting_integrations';

    protected $fillable = [
        'tenant_id',
        'hl7_enabled',
        'fhir_enabled',
        'webhook_enabled',
        'api_access_enabled',
        'third_party_integration_enabled',
        'pacs_enabled',
        'payment_gateway_enabled',
    ];

    protected $casts = [
        'hl7_enabled' => 'boolean',
        'fhir_enabled' => 'boolean',
        'webhook_enabled' => 'boolean',
        'api_access_enabled' => 'boolean',
        'third_party_integration_enabled' => 'boolean',
        'pacs_enabled' => 'boolean',
        'payment_gateway_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        return $query;
    }
}
