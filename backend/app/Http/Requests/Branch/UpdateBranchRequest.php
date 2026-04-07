<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('branch.update');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');
        $branchId = (int) $this->route('id');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($branchId),
            ],
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('branches', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($branchId),
            ],
            'type' => 'nullable|string|max:100',
            'is_main' => 'sometimes|boolean',
            'status' => 'required|in:active,inactive,suspended',
            'registration_no' => 'nullable|string|max:100',
            'license_no' => 'nullable|string|max:100',
            'tax_no' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'emergency_phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'required|timezone',
            'currency' => 'required|string|max:10',
            'opening_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
