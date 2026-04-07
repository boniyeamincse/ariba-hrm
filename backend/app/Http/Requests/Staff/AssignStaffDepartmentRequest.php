<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AssignStaffDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.assign.department');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');

        $rules = [
            'department_id' => ['required', 'integer'],
        ];

        if (Schema::hasTable('departments')) {
            $rules['department_id'][] = Rule::exists('departments', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId));
        }

        return $rules;
    }
}
