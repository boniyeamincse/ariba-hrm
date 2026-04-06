<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingLocalization extends Model
{
    protected $table = 'setting_localizations';

    protected $fillable = [
        'tenant_id',
        'default_language',
        'supported_languages',
        'timezone',
        'currency',
        'number_format',
        'date_format',
        'time_format',
    ];

    protected $casts = [
        'supported_languages' => 'array',
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
