<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingSecurity extends Model
{
    protected $table = 'setting_securities';

    protected $fillable = [
        'tenant_id',
        'password_min_length',
        'password_require_uppercase',
        'password_require_lowercase',
        'password_require_number',
        'password_require_special_char',
        'password_expiry_days',
        'login_attempt_limit',
        'lockout_duration_minutes',
        'mfa_enabled',
        'session_timeout_minutes',
        'ip_whitelist',
        'trusted_devices_enabled',
    ];

    protected $casts = [
        'password_require_uppercase' => 'boolean',
        'password_require_lowercase' => 'boolean',
        'password_require_number' => 'boolean',
        'password_require_special_char' => 'boolean',
        'mfa_enabled' => 'boolean',
        'trusted_devices_enabled' => 'boolean',
        'ip_whitelist' => 'array',
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
