<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'insurance_policy_id',
        'invoice_id',
        'claim_no',
        'claim_amount',
        'approved_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'claim_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];
}
