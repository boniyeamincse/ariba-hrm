<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'doctor_id',
        'opd_queue_id',
        'complaint',
        'assessment',
        'plan',
        'diagnosis_code',
        'follow_up_at',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
    ];
}
