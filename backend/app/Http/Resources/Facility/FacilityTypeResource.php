<?php

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'facilities_count' => $this->whenCounted('facilities'),
        ];
    }
}
