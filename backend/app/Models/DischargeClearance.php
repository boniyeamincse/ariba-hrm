<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DischargeClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ipd_admission_id',
        'invoice_id',
        'pharmacy_cleared',
        'lab_cleared',
        'billing_cleared',
        'cleared_by',
        'cleared_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'pharmacy_cleared' => 'boolean',
        'lab_cleared' => 'boolean',
        'billing_cleared' => 'boolean',
        'cleared_at' => 'datetime',
    ];
}
