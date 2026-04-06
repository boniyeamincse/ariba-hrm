<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'patient_id',
        'order_type',
        'test_name',
        'notes',
        'status',
        'routed_module',
        'routed_reference',
    ];
}
