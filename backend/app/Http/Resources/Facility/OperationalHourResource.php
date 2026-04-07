<?php

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationalHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'facility_id' => $this->facility_id,
            'day_of_week' => $this->day_of_week,
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_closed' => $this->is_closed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
