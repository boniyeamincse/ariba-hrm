<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecuritySettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'password_min_length' => $this->password_min_length,
            'password_require_uppercase' => $this->password_require_uppercase,
            'password_require_lowercase' => $this->password_require_lowercase,
            'password_require_number' => $this->password_require_number,
            'password_require_special_char' => $this->password_require_special_char,
            'password_expiry_days' => $this->password_expiry_days,
            'login_attempt_limit' => $this->login_attempt_limit,
            'lockout_duration_minutes' => $this->lockout_duration_minutes,
            'mfa_enabled' => $this->mfa_enabled,
            'session_timeout_minutes' => $this->session_timeout_minutes,
            'ip_whitelist' => $this->ip_whitelist,
            'trusted_devices_enabled' => $this->trusted_devices_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
