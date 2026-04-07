<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'license_type',
        'license_number',
        'issuing_authority',
        'issued_at',
        'expires_at',
        'is_verified',
        'remarks',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
