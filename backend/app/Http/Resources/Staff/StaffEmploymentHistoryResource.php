<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffEmploymentHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'staff_id' => $this->staff_id,
            'action' => $this->action,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'remarks' => $this->remarks,
            'effective_date' => $this->effective_date,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator?->id,
                    'name' => $this->creator?->name,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
