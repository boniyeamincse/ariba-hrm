<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    public $timestamps = false;

    protected $casts = [
        'file_size' => 'integer',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
