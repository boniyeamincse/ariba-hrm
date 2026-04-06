<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.branding.update');
    }

    public function rules(): array
    {
        return [
            'primary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'theme_mode' => 'required|in:light,dark,auto',
            'login_page_title' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'watermark_text' => 'nullable|string|max:255',
            'white_label_enabled' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color.regex' => 'Primary color must be a valid hex color (e.g., #FF5733)',
            'secondary_color.regex' => 'Secondary color must be a valid hex color',
        ];
    }
}
