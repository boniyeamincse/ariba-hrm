<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingPharmacy extends Model
{
    protected $table = 'setting_pharmacies';

    protected $fillable = [
        'tenant_id',
        'dispense_prefix',
        'enable_batch_tracking',
        'enable_expiry_alert',
        'low_stock_threshold_mode',
        'allow_partial_dispense',
        'enforce_fefo',
    ];

    protected $casts = [
        'enable_batch_tracking' => 'boolean',
        'enable_expiry_alert' => 'boolean',
        'allow_partial_dispense' => 'boolean',
        'enforce_fefo' => 'boolean',
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
