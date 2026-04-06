<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SettingEmailConfig extends Model
{
    protected $table = 'setting_email_configs';

    protected $fillable = [
        'tenant_id',
        'mail_driver',
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_password',
        'smtp_encryption',
        'from_email',
        'from_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getSmtpPasswordAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setSmtpPasswordAttribute($value)
    {
        $this->attributes['smtp_password'] = $value ? Crypt::encrypt($value) : null;
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
