<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MortuaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'deceased_name',
        'cause_of_death',
        'time_of_death',
        'status',
        'notes',
    ];

    protected $casts = [
        'time_of_death' => 'datetime',
    ];
}
