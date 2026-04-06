<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingNotification extends Model
{
    protected $table = 'setting_notifications';

    protected $fillable = [
        'tenant_id',
        'email_notifications_enabled',
        'sms_notifications_enabled',
        'push_notifications_enabled',
        'whatsapp_notifications_enabled',
        'appointment_reminder_enabled',
        'billing_alert_enabled',
        'lab_result_notification_enabled',
        'discharge_notification_enabled',
    ];

    protected $casts = [
        'email_notifications_enabled' => 'boolean',
        'sms_notifications_enabled' => 'boolean',
        'push_notifications_enabled' => 'boolean',
        'whatsapp_notifications_enabled' => 'boolean',
        'appointment_reminder_enabled' => 'boolean',
        'billing_alert_enabled' => 'boolean',
        'lab_result_notification_enabled' => 'boolean',
        'discharge_notification_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeTenant($query, ?int $tenantId = null)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        return $query;
    }
}
