<?php

namespace App\Http\Requests\Facility;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('facility.create');
    }

    public function rules(): array
    {
        $tenantId = (int) $this->attributes->get('tenant_id');

        return [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'facility_type_id' => [
                'nullable',
                'integer',
                Rule::exists('facility_types', 'id')->where(function ($query) use ($tenantId): void {
                    $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                }),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('facilities', 'code')->where(fn ($query) => $query->where('branch_id', $this->input('branch_id'))),
            ],
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('facilities', 'slug')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'building_name' => 'nullable|string|max:150',
            'floor_no' => 'nullable|string|max:30',
            'wing' => 'nullable|string|max:100',
            'room_prefix' => 'nullable|string|max:20',
            'service_point_type' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,maintenance',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'extension' => 'nullable|string|max:20',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string',
        ];
    }
}
