<?php

namespace App\Http\Requests\Api\V1\Rbac;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('rbac:update_permission');
    }

    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'module_key' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
