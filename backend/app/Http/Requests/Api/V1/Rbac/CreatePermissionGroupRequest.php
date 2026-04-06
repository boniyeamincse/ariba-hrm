<?php

namespace App\Http\Requests\Api\V1\Rbac;

use Illuminate\Foundation\Http\FormRequest;

class CreatePermissionGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('rbac:manage_groups');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:50', 'unique:permission_groups,key'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.unique' => 'Permission group key must be unique',
        ];
    }
}
