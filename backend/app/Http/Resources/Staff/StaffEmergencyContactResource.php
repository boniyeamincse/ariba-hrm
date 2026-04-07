<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffEmergencyContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'staff_id' => $this->staff_id,
            'name' => $this->name,
            'relationship' => $this->relationship,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
