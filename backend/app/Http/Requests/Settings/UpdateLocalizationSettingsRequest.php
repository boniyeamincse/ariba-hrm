<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocalizationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'default_language' => 'required|in:en,es,fr,de,hi',
            'supported_languages' => 'required|array|min:1',
            'supported_languages.*' => 'string|in:en,es,fr,de,hi',
            'timezone' => 'required|timezone',
            'currency' => 'required|in:USD,EUR,INR,GBP',
            'number_format' => 'required|in:1,000.00,1.000,00,1000.00',
            'date_format' => 'required|in:YYYY-MM-DD,DD-MM-YYYY,MM/DD/YYYY',
            'time_format' => 'required|in:HH:mm:ss,hh:mm:ss A',
        ];
    }
}
