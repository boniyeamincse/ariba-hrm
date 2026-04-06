<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'email_notifications_enabled' => $this->email_notifications_enabled,
            'sms_notifications_enabled' => $this->sms_notifications_enabled,
            'push_notifications_enabled' => $this->push_notifications_enabled,
            'whatsapp_notifications_enabled' => $this->whatsapp_notifications_enabled,
            'appointment_reminder_enabled' => $this->appointment_reminder_enabled,
            'billing_alert_enabled' => $this->billing_alert_enabled,
            'lab_result_notification_enabled' => $this->lab_result_notification_enabled,
            'discharge_notification_enabled' => $this->discharge_notification_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
