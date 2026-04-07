<?php

namespace App\Http\Requests\Facility;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacilityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('facility.status.update');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:active,inactive,maintenance',
        ];
    }
}
