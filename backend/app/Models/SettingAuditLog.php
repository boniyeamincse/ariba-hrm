<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingAuditLog extends Model
{
    protected $table = 'setting_audit_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'section',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        return $query;
    }

    public function scopeSection($query, string $section)
    {
        return $query->where('section', $section);
    }
}
