<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'patient_visit_id',
        'barcode',
        'status',
        'collected_by',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];
}
