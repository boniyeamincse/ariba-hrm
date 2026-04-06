<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingGeneral extends Model
{
    protected $table = 'setting_generals';

    protected $fillable = [
        'tenant_id',
        'hospital_name',
        'hospital_code',
        'registration_no',
        'license_no',
        'email',
        'phone',
        'emergency_phone',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'zip_code',
        'timezone',
        'currency',
        'language',
        'date_format',
        'time_format',
        'logo_url',
        'favicon_url',
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
