<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.status.update');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:active,inactive,probation,suspended,terminated,resigned',
            'remarks' => 'nullable|string',
            'effective_date' => 'nullable|date',
        ];
    }
}
