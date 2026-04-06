<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.security.update');
    }

    public function rules(): array
    {
        return [
            'password_min_length' => 'required|integer|min:6|max:20',
            'password_require_uppercase' => 'required|boolean',
            'password_require_lowercase' => 'required|boolean',
            'password_require_number' => 'required|boolean',
            'password_require_special_char' => 'required|boolean',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'login_attempt_limit' => 'required|integer|min:3|max:20',
            'lockout_duration_minutes' => 'required|integer|min:5|max:1440',
            'mfa_enabled' => 'required|boolean',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'ip_whitelist' => 'nullable|array',
            'ip_whitelist.*' => 'ip',
            'trusted_devices_enabled' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'ip_whitelist.*.ip' => 'Each entry must be a valid IPv4 or IPv6 address',
        ];
    }
}
