<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.clinical.update');
    }

    public function rules(): array
    {
        return [
            'uhid_prefix' => 'required|string|max:20',
            'opd_prefix' => 'required|string|max:20',
            'ipd_prefix' => 'required|string|max:20',
            'prescription_prefix' => 'required|string|max:20',
            'lab_order_prefix' => 'required|string|max:20',
            'radiology_order_prefix' => 'required|string|max:20',
            'enable_eprescription' => 'required|boolean',
            'enable_clinical_notes_template' => 'required|boolean',
            'enable_icd10' => 'required|boolean',
            'enable_followup_reminder' => 'required|boolean',
        ];
    }
}
