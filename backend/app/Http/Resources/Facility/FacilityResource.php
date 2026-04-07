<?php

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'branch_id' => $this->branch_id,
            'facility_type_id' => $this->facility_type_id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'building_name' => $this->building_name,
            'floor_no' => $this->floor_no,
            'wing' => $this->wing,
            'room_prefix' => $this->room_prefix,
            'service_point_type' => $this->service_point_type,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,
            'extension' => $this->extension,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch?->id,
                    'name' => $this->branch?->name,
                    'code' => $this->branch?->code,
                    'status' => $this->branch?->status,
                ];
            }),
            'facility_type' => $this->whenLoaded('facilityType', function () {
                return [
                    'id' => $this->facilityType?->id,
                    'key' => $this->facilityType?->key,
                    'name' => $this->facilityType?->name,
                ];
            }),
            'department_count' => $this->whenCounted('departments'),
            'user_count' => $this->whenCounted('users'),
            'operational_hours_count' => $this->whenCounted('operationalHours'),
        ];
    }
}
