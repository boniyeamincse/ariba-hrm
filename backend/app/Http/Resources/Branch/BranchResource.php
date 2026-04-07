<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'is_main' => $this->is_main,
            'status' => $this->status,
            'registration_no' => $this->registration_no,
            'license_no' => $this->license_no,
            'tax_no' => $this->tax_no,
            'email' => $this->email,
            'phone' => $this->phone,
            'emergency_phone' => $this->emergency_phone,
            'website' => $this->website,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'opening_date' => $this->opening_date,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'facilities_count' => $this->whenCounted('facilities'),
        ];
    }
}
