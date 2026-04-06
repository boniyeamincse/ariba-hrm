<?php

namespace App\Http\Requests\Auth;

use App\Services\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => app(PasswordPolicy::class)->rules(confirmed: true),
        ];
    }
}
