<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpdQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'patient_visit_id',
        'token_no',
        'priority',
        'status',
        'queued_at',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
    ];
}
