<?php

namespace App\Repositories;

use App\Models\SettingAuditLog;
use App\Models\SettingAppointment;
use App\Models\SettingBilling;
use App\Models\SettingBranding;
use App\Models\SettingClinical;
use App\Models\SettingEmailConfig;
use App\Models\SettingGeneral;
use App\Models\SettingIntegration;
use App\Models\SettingIpd;
use App\Models\SettingLab;
use App\Models\SettingLocalization;
use App\Models\SettingNotification;
use App\Models\SettingPharmacy;
use App\Models\SettingSecurity;
use App\Models\SettingSmsConfig;
use App\Models\SettingTemplate;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

class SettingsRepository
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    // ─── General ─────────────────────────────────────────────────────────────

    public function getGeneral(): SettingGeneral
    {
        return SettingGeneral::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['hospital_name' => 'My Hospital', 'hospital_code' => 'HOSP', 'timezone' => 'UTC', 'currency' => 'USD', 'language' => 'en', 'date_format' => 'YYYY-MM-DD', 'time_format' => 'HH:mm:ss']
        );
    }

    public function updateGeneral(array $data): SettingGeneral
    {
        $setting = $this->getGeneral();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Branding ─────────────────────────────────────────────────────────────

    public function getBranding(): SettingBranding
    {
        return SettingBranding::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['theme_mode' => 'light', 'white_label_enabled' => false]
        );
    }

    public function updateBranding(array $data): SettingBranding
    {
        $setting = $this->getBranding();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Localization ─────────────────────────────────────────────────────────

    public function getLocalization(): SettingLocalization
    {
        return SettingLocalization::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['default_language' => 'en', 'supported_languages' => ['en'], 'timezone' => 'UTC', 'currency' => 'USD', 'number_format' => '1,000.00', 'date_format' => 'YYYY-MM-DD', 'time_format' => 'HH:mm:ss']
        );
    }

    public function updateLocalization(array $data): SettingLocalization
    {
        $setting = $this->getLocalization();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Notifications ────────────────────────────────────────────────────────

    public function getNotifications(): SettingNotification
    {
        return SettingNotification::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['email_notifications_enabled' => true, 'sms_notifications_enabled' => false, 'push_notifications_enabled' => false, 'whatsapp_notifications_enabled' => false, 'appointment_reminder_enabled' => true, 'billing_alert_enabled' => true, 'lab_result_notification_enabled' => true, 'discharge_notification_enabled' => true]
        );
    }

    public function updateNotifications(array $data): SettingNotification
    {
        $setting = $this->getNotifications();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Email Config ─────────────────────────────────────────────────────────

    public function getEmailConfig(): SettingEmailConfig
    {
        return SettingEmailConfig::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['mail_driver' => 'smtp', 'smtp_encryption' => 'tls']
        );
    }

    public function updateEmailConfig(array $data): SettingEmailConfig
    {
        $setting = $this->getEmailConfig();

        // Never overwrite password if an empty value is sent
        if (array_key_exists('smtp_password', $data) && empty($data['smtp_password'])) {
            unset($data['smtp_password']);
        }

        $setting->update($data);
        return $setting->fresh();
    }

    // ─── SMS Config ───────────────────────────────────────────────────────────

    public function getSmsConfig(): SettingSmsConfig
    {
        return SettingSmsConfig::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            []
        );
    }

    public function updateSmsConfig(array $data): SettingSmsConfig
    {
        $setting = $this->getSmsConfig();

        if (array_key_exists('api_key', $data) && empty($data['api_key'])) {
            unset($data['api_key']);
        }

        if (array_key_exists('api_secret', $data) && empty($data['api_secret'])) {
            unset($data['api_secret']);
        }

        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Billing ──────────────────────────────────────────────────────────────

    public function getBilling(): SettingBilling
    {
        return SettingBilling::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['invoice_prefix' => 'INV', 'receipt_prefix' => 'RCP', 'estimate_prefix' => 'EST', 'refund_prefix' => 'REF', 'tax_name' => 'GST', 'tax_percentage' => 18.00, 'auto_generate_invoice_number' => true, 'allow_manual_discount' => false, 'require_discount_approval' => true]
        );
    }

    public function updateBilling(array $data): SettingBilling
    {
        $setting = $this->getBilling();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Clinical ─────────────────────────────────────────────────────────────

    public function getClinical(): SettingClinical
    {
        return SettingClinical::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['uhid_prefix' => 'UHID', 'opd_prefix' => 'OPD', 'ipd_prefix' => 'IPD', 'prescription_prefix' => 'RX', 'lab_order_prefix' => 'LAB', 'radiology_order_prefix' => 'RAD', 'enable_eprescription' => false, 'enable_clinical_notes_template' => true, 'enable_icd10' => true, 'enable_followup_reminder' => true]
        );
    }

    public function updateClinical(array $data): SettingClinical
    {
        $setting = $this->getClinical();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Appointments ─────────────────────────────────────────────────────────

    public function getAppointments(): SettingAppointment
    {
        return SettingAppointment::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['default_slot_duration' => 30, 'max_patients_per_slot' => 3, 'allow_overbooking' => false, 'overbooking_limit' => 0, 'booking_lead_days' => 30, 'cancellation_window_hours' => 24, 'auto_confirm_appointments' => false]
        );
    }

    public function updateAppointments(array $data): SettingAppointment
    {
        $setting = $this->getAppointments();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── IPD ──────────────────────────────────────────────────────────────────

    public function getIpd(): SettingIpd
    {
        return SettingIpd::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['admission_prefix' => 'ADM', 'discharge_prefix' => 'DIS', 'bed_transfer_prefix' => 'TRF', 'enable_bed_reservation' => true, 'allow_direct_admission' => false, 'require_guarantor_info' => true, 'enable_discharge_approval' => true]
        );
    }

    public function updateIpd(array $data): SettingIpd
    {
        $setting = $this->getIpd();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Pharmacy ─────────────────────────────────────────────────────────────

    public function getPharmacy(): SettingPharmacy
    {
        return SettingPharmacy::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['dispense_prefix' => 'DISP', 'enable_batch_tracking' => true, 'enable_expiry_alert' => true, 'low_stock_threshold_mode' => 'percentage', 'allow_partial_dispense' => true, 'enforce_fefo' => true]
        );
    }

    public function updatePharmacy(array $data): SettingPharmacy
    {
        $setting = $this->getPharmacy();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Lab ──────────────────────────────────────────────────────────────────

    public function getLab(): SettingLab
    {
        return SettingLab::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['sample_prefix' => 'SAM', 'report_prefix' => 'REP', 'barcode_enabled' => true, 'qr_report_enabled' => true, 'critical_alert_enabled' => true, 'result_approval_required' => true]
        );
    }

    public function updateLab(array $data): SettingLab
    {
        $setting = $this->getLab();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Integration ──────────────────────────────────────────────────────────

    public function getIntegrations(): SettingIntegration
    {
        return SettingIntegration::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['hl7_enabled' => false, 'fhir_enabled' => false, 'webhook_enabled' => false, 'api_access_enabled' => true, 'third_party_integration_enabled' => false, 'pacs_enabled' => false, 'payment_gateway_enabled' => false]
        );
    }

    public function updateIntegrations(array $data): SettingIntegration
    {
        $setting = $this->getIntegrations();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Security ─────────────────────────────────────────────────────────────

    public function getSecurity(): SettingSecurity
    {
        return SettingSecurity::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            ['password_min_length' => 8, 'password_require_uppercase' => true, 'password_require_lowercase' => true, 'password_require_number' => true, 'password_require_special_char' => false, 'password_expiry_days' => 90, 'login_attempt_limit' => 5, 'lockout_duration_minutes' => 30, 'mfa_enabled' => false, 'session_timeout_minutes' => 30, 'trusted_devices_enabled' => false]
        );
    }

    public function updateSecurity(array $data): SettingSecurity
    {
        $setting = $this->getSecurity();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Templates ────────────────────────────────────────────────────────────

    public function getTemplates(): SettingTemplate
    {
        return SettingTemplate::firstOrCreate(
            ['tenant_id' => $this->tenantId],
            []
        );
    }

    public function updateTemplates(array $data): SettingTemplate
    {
        $setting = $this->getTemplates();
        $setting->update($data);
        return $setting->fresh();
    }

    // ─── Audit Logs ───────────────────────────────────────────────────────────

    public function logAudit(string $section, array $oldValues, array $newValues): void
    {
        $changed = array_diff_assoc($newValues, $oldValues);
        if (empty($changed)) {
            return;
        }

        SettingAuditLog::create([
            'tenant_id' => $this->tenantId,
            'user_id' => auth()->id(),
            'section' => $section,
            'action' => 'update',
            'old_values' => array_intersect_key($oldValues, $changed),
            'new_values' => $changed,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getAuditLogs(array $filters = []): LengthAwarePaginator
    {
        $query = SettingAuditLog::where('tenant_id', $this->tenantId)
            ->with('user')
            ->orderByDesc('created_at');

        if (!empty($filters['section'])) {
            $query->where('section', $filters['section']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $perPage = min((int) ($filters['per_page'] ?? 20), 100);
        return $query->paginate($perPage);
    }
}
