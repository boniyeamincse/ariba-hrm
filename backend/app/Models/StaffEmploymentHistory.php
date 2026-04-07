<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEmploymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'action',
        'old_status',
        'new_status',
        'remarks',
        'effective_date',
        'created_by',
    ];

    public $timestamps = false;

    protected $casts = [
        'effective_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
