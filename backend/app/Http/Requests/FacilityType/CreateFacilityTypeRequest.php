<?php

namespace App\Http\Requests\FacilityType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFacilityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('facility.create');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');

        return [
            'key' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('facility_types', 'key')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
