<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'default_slot_duration' => $this->default_slot_duration,
            'max_patients_per_slot' => $this->max_patients_per_slot,
            'allow_overbooking' => $this->allow_overbooking,
            'overbooking_limit' => $this->overbooking_limit,
            'booking_lead_days' => $this->booking_lead_days,
            'cancellation_window_hours' => $this->cancellation_window_hours,
            'auto_confirm_appointments' => $this->auto_confirm_appointments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
