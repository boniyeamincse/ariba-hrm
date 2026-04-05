<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodTransfusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'blood_group',
        'units',
        'transfused_at',
    ];

    protected $casts = [
        'transfused_at' => 'datetime',
    ];
}
