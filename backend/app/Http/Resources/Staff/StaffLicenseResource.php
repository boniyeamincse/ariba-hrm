<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffLicenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'staff_id' => $this->staff_id,
            'license_type' => $this->license_type,
            'license_number' => $this->license_number,
            'issuing_authority' => $this->issuing_authority,
            'issued_at' => $this->issued_at,
            'expires_at' => $this->expires_at,
            'is_verified' => $this->is_verified,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
