<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpdAdmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'patient_visit_id',
        'admitted_by',
        'bed_id',
        'reason',
        'admitted_at',
        'discharged_at',
        'status',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public function wardRounds(): HasMany
    {
        return $this->hasMany(WardRound::class);
    }

    public function nursingNotes(): HasMany
    {
        return $this->hasMany(NursingNote::class);
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class);
    }
}
