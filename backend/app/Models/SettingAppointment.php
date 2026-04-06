<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingAppointment extends Model
{
    protected $table = 'setting_appointments';

    protected $fillable = [
        'tenant_id',
        'default_slot_duration',
        'max_patients_per_slot',
        'allow_overbooking',
        'overbooking_limit',
        'booking_lead_days',
        'cancellation_window_hours',
        'auto_confirm_appointments',
    ];

    protected $casts = [
        'allow_overbooking' => 'boolean',
        'auto_confirm_appointments' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        return $query;
    }
}
