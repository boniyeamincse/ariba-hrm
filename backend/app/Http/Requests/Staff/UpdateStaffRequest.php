<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.update');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');
        $staffId = (int) $this->route('id');

        $departmentRules = ['nullable', 'integer'];
        if (Schema::hasTable('departments')) {
            $departmentRules[] = Rule::exists('departments', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId));
        }

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('staff', 'employee_code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($staffId),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'marital_status' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'photo_path' => 'nullable|string|max:2048',
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'facility_id' => [
                'nullable',
                'integer',
                Rule::exists('facilities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'department_id' => $departmentRules,
            'manager_staff_id' => [
                'nullable',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::notIn([$staffId]),
            ],
            'designation' => 'nullable|string|max:150',
            'staff_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:50',
            'join_date' => 'required|date',
            'confirmation_date' => 'nullable|date|after_or_equal:join_date',
            'probation_end_date' => 'nullable|date|after_or_equal:join_date',
            'exit_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,probation,suspended,terminated,resigned',
            'payroll_group' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
