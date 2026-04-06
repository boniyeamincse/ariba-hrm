<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'hospital_name' => 'required|string|max:255',
            'hospital_code' => 'required|string|max:50',
            'registration_no' => 'nullable|string|max:100',
            'license_no' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'emergency_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'timezone' => 'required|timezone',
            'currency' => 'required|in:USD,EUR,INR,GBP,AUD',
            'language' => 'required|in:en,es,fr,de,hi',
            'date_format' => 'required|in:YYYY-MM-DD,DD-MM-YYYY,MM/DD/YYYY',
            'time_format' => 'required|in:HH:mm:ss,hh:mm:ss A',
            'logo_url' => 'nullable|url',
            'favicon_url' => 'nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_name.required' => 'Hospital name is required',
            'timezone.required' => 'Timezone is required',
            'timezone.timezone' => 'Timezone must be a valid timezone',
            'currency.in' => 'Currency must be one of: USD, EUR, INR, GBP, AUD',
        ];
    }
}
