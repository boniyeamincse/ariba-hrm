<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingLab extends Model
{
    protected $table = 'setting_labs';

    protected $fillable = [
        'tenant_id',
        'sample_prefix',
        'report_prefix',
        'barcode_enabled',
        'qr_report_enabled',
        'critical_alert_enabled',
        'result_approval_required',
    ];

    protected $casts = [
        'barcode_enabled' => 'boolean',
        'qr_report_enabled' => 'boolean',
        'critical_alert_enabled' => 'boolean',
        'result_approval_required' => 'boolean',
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
