<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ipd_admission_id',
        'doctor_id',
        'notes',
        'rounded_at',
    ];

    protected $casts = [
        'rounded_at' => 'datetime',
    ];
}
