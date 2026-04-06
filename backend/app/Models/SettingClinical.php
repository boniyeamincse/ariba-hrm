<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingClinical extends Model
{
    protected $table = 'setting_clinicals';

    protected $fillable = [
        'tenant_id',
        'uhid_prefix',
        'opd_prefix',
        'ipd_prefix',
        'prescription_prefix',
        'lab_order_prefix',
        'radiology_order_prefix',
        'enable_eprescription',
        'enable_clinical_notes_template',
        'enable_icd10',
        'enable_followup_reminder',
    ];

    protected $casts = [
        'enable_eprescription' => 'boolean',
        'enable_clinical_notes_template' => 'boolean',
        'enable_icd10' => 'boolean',
        'enable_followup_reminder' => 'boolean',
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
