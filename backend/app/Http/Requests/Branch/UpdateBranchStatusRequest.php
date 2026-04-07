<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('branch.status.update');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:active,inactive,suspended',
        ];
    }
}
