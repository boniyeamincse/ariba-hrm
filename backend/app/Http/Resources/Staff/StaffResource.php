<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'blood_group' => $this->blood_group,
            'marital_status' => $this->marital_status,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'email' => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'photo_path' => $this->photo_path,
            'branch_id' => $this->branch_id,
            'facility_id' => $this->facility_id,
            'department_id' => $this->department_id,
            'designation' => $this->designation,
            'staff_type' => $this->staff_type,
            'category' => $this->category,
            'manager_staff_id' => $this->manager_staff_id,
            'employment_type' => $this->employment_type,
            'join_date' => $this->join_date,
            'confirmation_date' => $this->confirmation_date,
            'probation_end_date' => $this->probation_end_date,
            'exit_date' => $this->exit_date,
            'status' => $this->status,
            'payroll_group' => $this->payroll_group,
            'basic_salary' => $this->basic_salary,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager?->id,
                    'employee_code' => $this->manager?->employee_code,
                    'full_name' => $this->manager?->full_name,
                    'designation' => $this->manager?->designation,
                    'status' => $this->manager?->status,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch?->id,
                    'name' => $this->branch?->name,
                    'code' => $this->branch?->code,
                    'status' => $this->branch?->status,
                ];
            }),
            'facility' => $this->whenLoaded('facility', function () {
                return [
                    'id' => $this->facility?->id,
                    'name' => $this->facility?->name,
                    'code' => $this->facility?->code,
                    'status' => $this->facility?->status,
                ];
            }),
            'user_account' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                    'status' => $this->user?->status,
                ];
            }),
            'licenses_count' => $this->whenCounted('licenses'),
            'documents_count' => $this->whenCounted('documents'),
            'emergency_contacts_count' => $this->whenCounted('emergencyContacts'),
            'subordinates_count' => $this->whenCounted('subordinates'),
        ];
    }
}
