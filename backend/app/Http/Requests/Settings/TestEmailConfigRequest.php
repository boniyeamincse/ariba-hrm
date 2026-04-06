<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class TestEmailConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'recipient_email' => 'required|email',
        ];
    }
}
