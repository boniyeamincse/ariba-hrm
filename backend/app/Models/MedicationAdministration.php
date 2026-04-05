<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationAdministration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ipd_admission_id',
        'nurse_id',
        'medicine_name',
        'dose',
        'route',
        'administered_at',
        'notes',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
    ];
}
