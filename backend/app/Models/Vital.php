<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'consultation_id',
        'recorded_by',
        'bp_systolic',
        'bp_diastolic',
        'temperature_c',
        'pulse',
        'spo2',
        'weight_kg',
        'height_cm',
        'bmi',
        'respiratory_rate',
        'pain_score',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'temperature_c' => 'decimal:1',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'bmi' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];
}
