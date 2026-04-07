<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $facilities = $this->whenLoaded('facilities');

        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'is_main' => $this->is_main,
            'city' => $this->city,
            'country' => $this->country,
            'facility_counts' => [
                'total' => $this->when(
                    $facilities !== null,
                    fn () => $facilities->count(),
                    $this->whenCounted('facilities')
                ),
                'active' => $this->when(
                    $facilities !== null,
                    fn () => $facilities->where('status', 'active')->count()
                ),
                'inactive' => $this->when(
                    $facilities !== null,
                    fn () => $facilities->where('status', 'inactive')->count()
                ),
                'maintenance' => $this->when(
                    $facilities !== null,
                    fn () => $facilities->where('status', 'maintenance')->count()
                ),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
