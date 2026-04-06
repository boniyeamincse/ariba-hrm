<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingIpd extends Model
{
    protected $table = 'setting_ipds';

    protected $fillable = [
        'tenant_id',
        'admission_prefix',
        'discharge_prefix',
        'bed_transfer_prefix',
        'enable_bed_reservation',
        'allow_direct_admission',
        'require_guarantor_info',
        'enable_discharge_approval',
    ];

    protected $casts = [
        'enable_bed_reservation' => 'boolean',
        'allow_direct_admission' => 'boolean',
        'require_guarantor_info' => 'boolean',
        'enable_discharge_approval' => 'boolean',
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
