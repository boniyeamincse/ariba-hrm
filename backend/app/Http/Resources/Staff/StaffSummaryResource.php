<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'employee_code' => $this->employee_code,
            'full_name' => $this->full_name,
            'designation' => $this->designation,
            'staff_type' => $this->staff_type,
            'category' => $this->category,
            'status' => $this->status,
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch?->id,
                    'name' => $this->branch?->name,
                    'code' => $this->branch?->code,
                ];
            }),
            'facility' => $this->whenLoaded('facility', function () {
                return [
                    'id' => $this->facility?->id,
                    'name' => $this->facility?->name,
                    'code' => $this->facility?->code,
                ];
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager?->id,
                    'employee_code' => $this->manager?->employee_code,
                    'full_name' => $this->manager?->full_name,
                ];
            }),
            'employment' => [
                'join_date' => $this->join_date,
                'confirmation_date' => $this->confirmation_date,
                'probation_end_date' => $this->probation_end_date,
                'exit_date' => $this->exit_date,
                'employment_type' => $this->employment_type,
                'payroll_group' => $this->payroll_group,
            ],
            'counts' => [
                'licenses' => $this->whenCounted('licenses'),
                'documents' => $this->whenCounted('documents'),
                'emergency_contacts' => $this->whenCounted('emergencyContacts'),
                'subordinates' => $this->whenCounted('subordinates'),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
