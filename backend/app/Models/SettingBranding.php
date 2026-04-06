<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingBranding extends Model
{
    protected $table = 'setting_brandings';

    protected $fillable = [
        'tenant_id',
        'primary_color',
        'secondary_color',
        'theme_mode',
        'login_page_title',
        'footer_text',
        'watermark_text',
        'white_label_enabled',
    ];

    protected $casts = [
        'white_label_enabled' => 'boolean',
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
