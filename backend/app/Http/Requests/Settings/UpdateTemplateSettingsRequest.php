<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'prescription_template' => 'nullable|string',
            'invoice_template' => 'nullable|string',
            'lab_report_template' => 'nullable|string',
            'discharge_summary_template' => 'nullable|string',
            'sick_leave_template' => 'nullable|string',
            'consent_form_template' => 'nullable|string',
        ];
    }
}
