<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgerySchedule extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'patient_visit_id',
        'operation_theater_id',
        'surgery_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
        'primary_surgeon_id',
        'anesthesiologist_id',
        'anesthesia_type',
        'pre_op_notes',
        'surgery_notes',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function operationTheater(): BelongsTo
    {
        return $this->belongsTo(OperationTheater::class);
    }

    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }

    public function primarySurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_surgeon_id');
    }

    public function anesthesiologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anesthesiologist_id');
    }
}
