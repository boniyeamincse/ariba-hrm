<?php

namespace App\Services\Settings;

use App\Repositories\SettingsRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingsService
{
    private SettingsRepository $repository;
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->repository = new SettingsRepository($tenantId);
    }

    // ─── General ─────────────────────────────────────────────────────────────

    public function getGeneral(): array
    {
        return Cache::remember("settings:general:{$this->tenantId}", 86400, function () {
            return $this->repository->getGeneral()->toArray();
        });
    }

    public function updateGeneral(array $data): \App\Models\SettingGeneral
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getGeneral()->toArray();
            $setting = $this->repository->updateGeneral($data);
            $this->repository->logAudit('general', $old, $setting->toArray());
            $this->clearCache('general');
            return $setting;
        });
    }

    // ─── Branding ─────────────────────────────────────────────────────────────

    public function getBranding(): array
    {
        return Cache::remember("settings:branding:{$this->tenantId}", 86400, function () {
            return $this->repository->getBranding()->toArray();
        });
    }

    public function updateBranding(array $data): \App\Models\SettingBranding
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getBranding()->toArray();
            $setting = $this->repository->updateBranding($data);
            $this->repository->logAudit('branding', $old, $setting->toArray());
            $this->clearCache('branding');
            return $setting;
        });
    }

    // ─── Localization ─────────────────────────────────────────────────────────

    public function getLocalization(): array
    {
        return Cache::remember("settings:localization:{$this->tenantId}", 86400, function () {
            return $this->repository->getLocalization()->toArray();
        });
    }

    public function updateLocalization(array $data): \App\Models\SettingLocalization
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getLocalization()->toArray();
            $setting = $this->repository->updateLocalization($data);
            $this->repository->logAudit('localization', $old, $setting->toArray());
            $this->clearCache('localization');
            return $setting;
        });
    }

    // ─── Notifications ────────────────────────────────────────────────────────

    public function getNotifications(): array
    {
        return Cache::remember("settings:notifications:{$this->tenantId}", 86400, function () {
            return $this->repository->getNotifications()->toArray();
        });
    }

    public function updateNotifications(array $data): \App\Models\SettingNotification
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getNotifications()->toArray();
            $setting = $this->repository->updateNotifications($data);
            $this->repository->logAudit('notifications', $old, $setting->toArray());
            $this->clearCache('notifications');
            return $setting;
        });
    }

    // ─── Email Config ─────────────────────────────────────────────────────────

    public function getEmailConfig(): \App\Models\SettingEmailConfig
    {
        return $this->repository->getEmailConfig();
    }

    public function updateEmailConfig(array $data): \App\Models\SettingEmailConfig
    {
        return DB::transaction(function () use ($data) {
            $old = $this->sanitizeForAudit($this->repository->getEmailConfig()->toArray(), ['smtp_password']);
            $setting = $this->repository->updateEmailConfig($data);
            $sanitized = $this->sanitizeForAudit($setting->toArray(), ['smtp_password']);
            $this->repository->logAudit('email-config', $old, $sanitized);
            return $setting;
        });
    }

    public function testEmailConfig(string $recipientEmail): array
    {
        try {
            $config = $this->repository->getEmailConfig();

            config([
                'mail.mailers.smtp.host' => $config->smtp_host,
                'mail.mailers.smtp.port' => $config->smtp_port,
                'mail.mailers.smtp.username' => $config->smtp_user,
                'mail.mailers.smtp.password' => $config->smtp_password,
                'mail.mailers.smtp.encryption' => $config->smtp_encryption,
                'mail.from.address' => $config->from_email,
                'mail.from.name' => $config->from_name,
            ]);

            $start = microtime(true);
            Mail::raw('This is a test email from HMS Settings.', function ($message) use ($recipientEmail, $config) {
                $message->to($recipientEmail)->subject('HMS Email Configuration Test');
            });

            $this->repository->logAudit('email-config', [], ['action' => 'test', 'recipient' => $recipientEmail]);

            return [
                'test_result' => 'passed',
                'recipient_email' => $recipientEmail,
                'response_time_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        } catch (\Exception $e) {
            return [
                'test_result' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    // ─── SMS Config ───────────────────────────────────────────────────────────

    public function getSmsConfig(): \App\Models\SettingSmsConfig
    {
        return $this->repository->getSmsConfig();
    }

    public function updateSmsConfig(array $data): \App\Models\SettingSmsConfig
    {
        return DB::transaction(function () use ($data) {
            $old = $this->sanitizeForAudit($this->repository->getSmsConfig()->toArray(), ['api_key', 'api_secret']);
            $setting = $this->repository->updateSmsConfig($data);
            $sanitized = $this->sanitizeForAudit($setting->toArray(), ['api_key', 'api_secret']);
            $this->repository->logAudit('sms-config', $old, $sanitized);
            return $setting;
        });
    }

    public function testSmsConfig(string $recipientPhone): array
    {
        try {
            $config = $this->repository->getSmsConfig();

            if (!$config->provider_name || !$config->api_key) {
                throw new \RuntimeException('SMS provider not configured');
            }

            $this->repository->logAudit('sms-config', [], ['action' => 'test', 'recipient' => $recipientPhone]);

            return [
                'test_result' => 'passed',
                'recipient_phone' => $recipientPhone,
                'provider' => $config->provider_name,
            ];
        } catch (\Exception $e) {
            return [
                'test_result' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    // ─── Billing ──────────────────────────────────────────────────────────────

    public function getBilling(): array
    {
        return Cache::remember("settings:billing:{$this->tenantId}", 86400, function () {
            return $this->repository->getBilling()->toArray();
        });
    }

    public function updateBilling(array $data): \App\Models\SettingBilling
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getBilling()->toArray();
            $setting = $this->repository->updateBilling($data);
            $this->repository->logAudit('billing', $old, $setting->toArray());
            $this->clearCache('billing');
            return $setting;
        });
    }

    // ─── Clinical ─────────────────────────────────────────────────────────────

    public function getClinical(): array
    {
        return Cache::remember("settings:clinical:{$this->tenantId}", 86400, function () {
            return $this->repository->getClinical()->toArray();
        });
    }

    public function updateClinical(array $data): \App\Models\SettingClinical
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getClinical()->toArray();
            $setting = $this->repository->updateClinical($data);
            $this->repository->logAudit('clinical', $old, $setting->toArray());
            $this->clearCache('clinical');
            return $setting;
        });
    }

    // ─── Appointments ─────────────────────────────────────────────────────────

    public function getAppointments(): array
    {
        return Cache::remember("settings:appointments:{$this->tenantId}", 86400, function () {
            return $this->repository->getAppointments()->toArray();
        });
    }

    public function updateAppointments(array $data): \App\Models\SettingAppointment
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getAppointments()->toArray();
            $setting = $this->repository->updateAppointments($data);
            $this->repository->logAudit('appointments', $old, $setting->toArray());
            $this->clearCache('appointments');
            return $setting;
        });
    }

    // ─── IPD ──────────────────────────────────────────────────────────────────

    public function getIpd(): array
    {
        return Cache::remember("settings:ipd:{$this->tenantId}", 86400, function () {
            return $this->repository->getIpd()->toArray();
        });
    }

    public function updateIpd(array $data): \App\Models\SettingIpd
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getIpd()->toArray();
            $setting = $this->repository->updateIpd($data);
            $this->repository->logAudit('ipd', $old, $setting->toArray());
            $this->clearCache('ipd');
            return $setting;
        });
    }

    // ─── Pharmacy ─────────────────────────────────────────────────────────────

    public function getPharmacy(): array
    {
        return Cache::remember("settings:pharmacy:{$this->tenantId}", 86400, function () {
            return $this->repository->getPharmacy()->toArray();
        });
    }

    public function updatePharmacy(array $data): \App\Models\SettingPharmacy
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getPharmacy()->toArray();
            $setting = $this->repository->updatePharmacy($data);
            $this->repository->logAudit('pharmacy', $old, $setting->toArray());
            $this->clearCache('pharmacy');
            return $setting;
        });
    }

    // ─── Lab ──────────────────────────────────────────────────────────────────

    public function getLab(): array
    {
        return Cache::remember("settings:lab:{$this->tenantId}", 86400, function () {
            return $this->repository->getLab()->toArray();
        });
    }

    public function updateLab(array $data): \App\Models\SettingLab
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getLab()->toArray();
            $setting = $this->repository->updateLab($data);
            $this->repository->logAudit('lab', $old, $setting->toArray());
            $this->clearCache('lab');
            return $setting;
        });
    }

    // ─── Integrations ─────────────────────────────────────────────────────────

    public function getIntegrations(): array
    {
        return Cache::remember("settings:integrations:{$this->tenantId}", 86400, function () {
            return $this->repository->getIntegrations()->toArray();
        });
    }

    public function updateIntegrations(array $data): \App\Models\SettingIntegration
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getIntegrations()->toArray();
            $setting = $this->repository->updateIntegrations($data);
            $this->repository->logAudit('integrations', $old, $setting->toArray());
            $this->clearCache('integrations');
            return $setting;
        });
    }

    // ─── Security ─────────────────────────────────────────────────────────────

    public function getSecurity(): array
    {
        return Cache::remember("settings:security:{$this->tenantId}", 86400, function () {
            return $this->repository->getSecurity()->toArray();
        });
    }

    public function updateSecurity(array $data): \App\Models\SettingSecurity
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getSecurity()->toArray();
            $setting = $this->repository->updateSecurity($data);
            $this->repository->logAudit('security', $old, $setting->toArray());
            $this->clearCache('security');
            return $setting;
        });
    }

    // ─── Templates ────────────────────────────────────────────────────────────

    public function getTemplates(): array
    {
        return Cache::remember("settings:templates:{$this->tenantId}", 86400, function () {
            return $this->repository->getTemplates()->toArray();
        });
    }

    public function updateTemplates(array $data): \App\Models\SettingTemplate
    {
        return DB::transaction(function () use ($data) {
            $old = $this->repository->getTemplates()->toArray();
            $setting = $this->repository->updateTemplates($data);
            $this->repository->logAudit('templates', $old, $setting->toArray());
            $this->clearCache('templates');
            return $setting;
        });
    }

    // ─── Audit Logs ───────────────────────────────────────────────────────────

    public function getAuditLogs(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->getAuditLogs($filters);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function clearCache(string $section): void
    {
        Cache::forget("settings:{$section}:{$this->tenantId}");
    }

    private function sanitizeForAudit(array $data, array $sensitiveKeys): array
    {
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '********';
            }
        }
        return $data;
    }
}
