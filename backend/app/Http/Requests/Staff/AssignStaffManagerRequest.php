<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStaffManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.assign.manager');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');
        $staffId = (int) $this->route('id');

        return [
            'manager_staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::notIn([$staffId]),
            ],
        ];
    }
}
