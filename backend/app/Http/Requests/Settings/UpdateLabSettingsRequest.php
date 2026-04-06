<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'sample_prefix' => 'required|string|max:20',
            'report_prefix' => 'required|string|max:20',
            'barcode_enabled' => 'required|boolean',
            'qr_report_enabled' => 'required|boolean',
            'critical_alert_enabled' => 'required|boolean',
            'result_approval_required' => 'required|boolean',
        ];
    }
}
