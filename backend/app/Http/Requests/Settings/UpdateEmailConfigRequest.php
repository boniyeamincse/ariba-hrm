<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'mail_driver' => 'required|in:smtp,sendmail,mailgun',
            'smtp_host' => 'required_if:mail_driver,smtp|string',
            'smtp_port' => 'required_if:mail_driver,smtp|integer|min:1|max:65535',
            'smtp_user' => 'required_if:mail_driver,smtp|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'required|in:tls,ssl',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'smtp_host.required_if' => 'SMTP host is required for SMTP driver',
            'smtp_port.required_if' => 'SMTP port is required for SMTP driver',
        ];
    }
}
