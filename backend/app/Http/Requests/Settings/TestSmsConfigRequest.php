<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class TestSmsConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'recipient_phone' => 'required|string|min:7|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_phone.required' => 'Recipient phone number is required',
        ];
    }
}
