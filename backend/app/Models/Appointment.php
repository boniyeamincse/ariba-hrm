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
        'rescheduled_from_id',
        'scheduled_at',
        'status',
        'cancelled_at',
        'cancel_reason',
        'visit_mode',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
