<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.license.manage');
    }

    public function rules(): array
    {
        return [
            'license_type' => 'required|string|max:100',
            'license_number' => 'required|string|max:100',
            'issuing_authority' => 'nullable|string|max:150',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:issued_at',
            'is_verified' => 'sometimes|boolean',
            'remarks' => 'nullable|string',
        ];
    }
}
