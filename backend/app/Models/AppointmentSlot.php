<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'doctor_id',
        'slot_date',
        'start_time',
        'end_time',
        'max_patients',
        'booked_count',
        'is_active',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'is_active' => 'boolean',
    ];
}
