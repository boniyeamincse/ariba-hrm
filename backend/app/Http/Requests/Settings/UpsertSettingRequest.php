<?php

namespace App\Http\Requests\Settings;

use App\Services\Settings\SettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'section' => [
                'sometimes',
                'string',
                Rule::in(SettingService::SECTIONS),
            ],
            'data' => ['required', 'array'],
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('section') && ! $this->has('section')) {
            $this->merge(['section' => $this->route('section')]);
        }
    }
}
