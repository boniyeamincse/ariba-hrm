<?php

namespace App\Http\Requests\Auth;

use App\Services\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'alpha_dash', 'max:63', 'unique:tenants,subdomain'],
            'database_name' => ['required', 'alpha_dash', 'max:64', 'unique:tenants,database_name'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => app(PasswordPolicy::class)->rules(confirmed: true),
        ];
    }
}
