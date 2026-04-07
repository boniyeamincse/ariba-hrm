<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.status.update');
    }

    public function rules(): array
    {
        return [
            'remarks' => 'nullable|string',
            'effective_date' => 'nullable|date',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tenantId = (int) $this->attributes->get('tenant_id');
            $staffId = (int) $this->route('id');

            $staff = Staff::query()->tenant($tenantId)->find($staffId);
            if (! $staff) {
                $validator->errors()->add('staff', 'Staff record not found in current tenant.');
                return;
            }

            if ($staff->status !== Staff::STATUS_PROBATION) {
                $validator->errors()->add('status', 'Only probation staff can be confirmed.');
            }
        });
    }
}
