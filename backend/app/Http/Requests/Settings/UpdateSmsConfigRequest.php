<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSmsConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'provider_name' => 'required|in:twilio,exotel,nexmo',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'sender_id' => 'required|string|max:20',
            'base_url' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'provider_name.in' => 'Provider must be one of: twilio, exotel, nexmo',
        ];
    }
}
