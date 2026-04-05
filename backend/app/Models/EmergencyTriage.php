<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyTriage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'patient_visit_id',
        'triage_level',
        'complaint',
        'vitals',
        'status',
    ];

    protected $casts = [
        'vitals' => 'array',
    ];
}
