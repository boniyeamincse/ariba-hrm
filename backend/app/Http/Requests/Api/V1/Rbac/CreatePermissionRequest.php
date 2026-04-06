<?php

namespace App\Http\Requests\Api\V1\Rbac;

use Illuminate\Foundation\Http\FormRequest;

class CreatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('rbac:create_permission');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'module_key' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required',
            'module_key.required' => 'Module key is required',
        ];
    }
}
