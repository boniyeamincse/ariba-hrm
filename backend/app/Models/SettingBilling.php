<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingBilling extends Model
{
    protected $table = 'setting_billings';

    protected $fillable = [
        'tenant_id',
        'invoice_prefix',
        'receipt_prefix',
        'estimate_prefix',
        'refund_prefix',
        'tax_name',
        'tax_percentage',
        'invoice_footer',
        'auto_generate_invoice_number',
        'allow_manual_discount',
        'require_discount_approval',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'auto_generate_invoice_number' => 'boolean',
        'allow_manual_discount' => 'boolean',
        'require_discount_approval' => 'boolean',
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
