<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UploadStaffDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('staff.document.manage');
    }

    public function rules(): array
    {
        return [
            'document_type' => 'required|string|max:100',
            'file' => 'nullable|file|max:10240',
            'file_path' => 'nullable|string|max:2048',
            'file_name' => 'nullable|string|max:255',
            'file_size' => 'nullable|integer|min:0',
            'mime_type' => 'nullable|string|max:150',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->hasFile('file') && ! $this->filled('file_path')) {
                $validator->errors()->add('file', 'Either file upload or file_path is required.');
            }
        });
    }
}
