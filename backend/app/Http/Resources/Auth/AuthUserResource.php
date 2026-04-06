<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'is_2fa_enabled' => (bool) ($this->is_2fa_enabled ?? $this->two_factor_enabled),
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'last_login_ip' => $this->last_login_ip,
        ];
    }
}
