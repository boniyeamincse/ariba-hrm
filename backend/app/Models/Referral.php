<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'patient_id',
        'referred_by',
        'referral_type',
        'target_department',
        'target_specialist',
        'external_facility',
        'reason',
        'clinical_notes',
        'status',
        'letter_pdf_path',
        'letter_generated_at',
        'follow_up_at',
    ];

    protected $casts = [
        'letter_generated_at' => 'datetime',
        'follow_up_at' => 'datetime',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
