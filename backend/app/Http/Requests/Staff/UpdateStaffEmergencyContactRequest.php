<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.emergency-contact.manage');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'relationship' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
        ];
    }
}
