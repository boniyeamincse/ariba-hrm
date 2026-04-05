<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'lab_order_id',
        'result_value',
        'unit',
        'reference_range',
        'is_abnormal',
        'validated_by',
        'validated_at',
        'report_content',
    ];

    protected $casts = [
        'is_abnormal' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }
}
