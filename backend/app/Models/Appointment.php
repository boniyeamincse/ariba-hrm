<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'doctor_id',
        'appointment_slot_id',
        'scheduled_at',
        'status',
        'visit_mode',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
}
