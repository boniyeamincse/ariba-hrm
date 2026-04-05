<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ipd_admission_id',
        'nurse_id',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}
