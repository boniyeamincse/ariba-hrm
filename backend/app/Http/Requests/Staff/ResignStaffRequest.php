<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;

class ResignStaffRequest extends FormRequest
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
            'exit_date' => 'nullable|date|after_or_equal:effective_date',
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

            if ($staff->status !== Staff::STATUS_ACTIVE) {
                $validator->errors()->add('status', 'Only active staff can resign.');
            }
        });
    }
}
