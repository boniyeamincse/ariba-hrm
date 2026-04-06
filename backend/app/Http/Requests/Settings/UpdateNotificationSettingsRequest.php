<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.notification.update');
    }

    public function rules(): array
    {
        return [
            'email_notifications_enabled' => 'required|boolean',
            'sms_notifications_enabled' => 'required|boolean',
            'push_notifications_enabled' => 'required|boolean',
            'whatsapp_notifications_enabled' => 'required|boolean',
            'appointment_reminder_enabled' => 'required|boolean',
            'billing_alert_enabled' => 'required|boolean',
            'lab_result_notification_enabled' => 'required|boolean',
            'discharge_notification_enabled' => 'required|boolean',
        ];
    }
}
