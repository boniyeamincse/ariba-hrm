<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'consultation_id',
        'sample_id',
        'lab_test_id',
        'notes',
        'status',
        'ordered_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];
}
