<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsurancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'insurance_provider_id',
        'policy_no',
        'coverage_limit',
        'used_amount',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'coverage_limit' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];
}
