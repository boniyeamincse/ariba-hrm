<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTemplate extends Model
{
    protected $table = 'setting_templates';

    protected $fillable = [
        'tenant_id',
        'prescription_template',
        'invoice_template',
        'lab_report_template',
        'discharge_summary_template',
        'sick_leave_template',
        'consent_form_template',
    ];

    protected $casts = [
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
