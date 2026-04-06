<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SettingSmsConfig extends Model
{
    protected $table = 'setting_sms_configs';

    protected $fillable = [
        'tenant_id',
        'provider_name',
        'api_key',
        'api_secret',
        'sender_id',
        'base_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getApiKeyAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getApiSecretAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = $value ? Crypt::encrypt($value) : null;
    }

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
