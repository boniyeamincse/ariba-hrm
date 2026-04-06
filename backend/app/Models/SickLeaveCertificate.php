<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SickLeaveCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'leave_from',
        'leave_to',
        'days_count',
        'reason',
        'doctor_signature_name',
        'pdf_path',
        'generated_at',
    ];

    protected $casts = [
        'leave_from' => 'date',
        'leave_to' => 'date',
        'generated_at' => 'datetime',
    ];
}
