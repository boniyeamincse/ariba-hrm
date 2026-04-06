<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'default_slot_duration' => 'required|integer|min:5|max:480',
            'max_patients_per_slot' => 'required|integer|min:1|max:50',
            'allow_overbooking' => 'required|boolean',
            'overbooking_limit' => 'required_if:allow_overbooking,true|integer|min:0',
            'booking_lead_days' => 'required|integer|min:1|max:365',
            'cancellation_window_hours' => 'required|integer|min:0|max:168',
            'auto_confirm_appointments' => 'required|boolean',
        ];
    }
}
