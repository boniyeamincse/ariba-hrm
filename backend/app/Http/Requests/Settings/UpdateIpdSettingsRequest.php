<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIpdSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'admission_prefix' => 'required|string|max:20',
            'discharge_prefix' => 'required|string|max:20',
            'bed_transfer_prefix' => 'required|string|max:20',
            'enable_bed_reservation' => 'required|boolean',
            'allow_direct_admission' => 'required|boolean',
            'require_guarantor_info' => 'required|boolean',
            'enable_discharge_approval' => 'required|boolean',
        ];
    }
}
