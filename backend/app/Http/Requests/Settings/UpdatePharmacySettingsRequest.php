<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePharmacySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'dispense_prefix' => 'required|string|max:20',
            'enable_batch_tracking' => 'required|boolean',
            'enable_expiry_alert' => 'required|boolean',
            'low_stock_threshold_mode' => 'required|in:percentage,quantity',
            'allow_partial_dispense' => 'required|boolean',
            'enforce_fefo' => 'required|boolean',
        ];
    }
}
