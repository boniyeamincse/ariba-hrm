<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.integration.update');
    }

    public function rules(): array
    {
        return [
            'hl7_enabled' => 'required|boolean',
            'fhir_enabled' => 'required|boolean',
            'webhook_enabled' => 'required|boolean',
            'api_access_enabled' => 'required|boolean',
            'third_party_integration_enabled' => 'required|boolean',
            'pacs_enabled' => 'required|boolean',
            'payment_gateway_enabled' => 'required|boolean',
        ];
    }
}
