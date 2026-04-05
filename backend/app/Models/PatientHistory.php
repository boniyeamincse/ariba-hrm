<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'allergies',
        'chronic_conditions',
        'surgical_history',
        'family_history',
        'immunization_records',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
