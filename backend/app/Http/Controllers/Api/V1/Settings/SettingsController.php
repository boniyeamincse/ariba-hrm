<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TestEmailConfigRequest;
use App\Http\Requests\Settings\TestSmsConfigRequest;
use App\Http\Requests\Settings\UpdateAppointmentSettingsRequest;
use App\Http\Requests\Settings\UpdateBillingSettingsRequest;
use App\Http\Requests\Settings\UpdateBrandingSettingsRequest;
use App\Http\Requests\Settings\UpdateClinicalSettingsRequest;
use App\Http\Requests\Settings\UpdateEmailConfigRequest;
use App\Http\Requests\Settings\UpdateGeneralSettingsRequest;
use App\Http\Requests\Settings\UpdateIntegrationSettingsRequest;
use App\Http\Requests\Settings\UpdateIpdSettingsRequest;
use App\Http\Requests\Settings\UpdateLabSettingsRequest;
use App\Http\Requests\Settings\UpdateLocalizationSettingsRequest;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use App\Http\Requests\Settings\UpdatePharmacySettingsRequest;
use App\Http\Requests\Settings\UpdateSecuritySettingsRequest;
use App\Http\Requests\Settings\UpdateSmsConfigRequest;
use App\Http\Requests\Settings\UpdateTemplateSettingsRequest;
use App\Http\Resources\Settings\AppointmentSettingsResource;
use App\Http\Resources\Settings\AuditLogResource;
use App\Http\Resources\Settings\BillingSettingsResource;
use App\Http\Resources\Settings\BrandingSettingsResource;
use App\Http\Resources\Settings\ClinicalSettingsResource;
use App\Http\Resources\Settings\EmailConfigResource;
use App\Http\Resources\Settings\GeneralSettingsResource;
use App\Http\Resources\Settings\IntegrationSettingsResource;
use App\Http\Resources\Settings\IpdSettingsResource;
use App\Http\Resources\Settings\LabSettingsResource;
use App\Http\Resources\Settings\LocalizationSettingsResource;
use App\Http\Resources\Settings\NotificationSettingsResource;
use App\Http\Resources\Settings\PharmacySettingsResource;
use App\Http\Resources\Settings\SecuritySettingsResource;
use App\Http\Resources\Settings\SmsConfigResource;
use App\Http\Resources\Settings\TemplateSettingsResource;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private function service(): SettingsService
    {
        return new SettingsService((int) request()->attributes->get('tenant_id'));
    }

    private function success(mixed $data, string $section, string $message = 'Settings retrieved successfully'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'section' => $section,
                'settings' => $data,
            ],
        ]);
    }

    // ─── General ─────────────────────────────────────────────────────────────

    public function getGeneral(): JsonResponse
    {
        $setting = $this->service()->getGeneral();
        return $this->success(new GeneralSettingsResource((object) $setting), 'general');
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateGeneral($request->validated());
        return $this->success(new GeneralSettingsResource($setting), 'general', 'Settings updated successfully');
    }

    // ─── Branding ─────────────────────────────────────────────────────────────

    public function getBranding(): JsonResponse
    {
        $setting = $this->service()->getBranding();
        return $this->success(new BrandingSettingsResource((object) $setting), 'branding');
    }

    public function updateBranding(UpdateBrandingSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateBranding($request->validated());
        return $this->success(new BrandingSettingsResource($setting), 'branding', 'Settings updated successfully');
    }

    // ─── Localization ─────────────────────────────────────────────────────────

    public function getLocalization(): JsonResponse
    {
        $setting = $this->service()->getLocalization();
        return $this->success(new LocalizationSettingsResource((object) $setting), 'localization');
    }

    public function updateLocalization(UpdateLocalizationSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateLocalization($request->validated());
        return $this->success(new LocalizationSettingsResource($setting), 'localization', 'Settings updated successfully');
    }

    // ─── Notifications ────────────────────────────────────────────────────────

    public function getNotifications(): JsonResponse
    {
        $setting = $this->service()->getNotifications();
        return $this->success(new NotificationSettingsResource((object) $setting), 'notifications');
    }

    public function updateNotifications(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateNotifications($request->validated());
        return $this->success(new NotificationSettingsResource($setting), 'notifications', 'Settings updated successfully');
    }

    // ─── Email Config ─────────────────────────────────────────────────────────

    public function getEmailConfig(): JsonResponse
    {
        $setting = $this->service()->getEmailConfig();
        return $this->success(new EmailConfigResource($setting), 'email-config');
    }

    public function updateEmailConfig(UpdateEmailConfigRequest $request): JsonResponse
    {
        $setting = $this->service()->updateEmailConfig($request->validated());
        return $this->success(new EmailConfigResource($setting), 'email-config', 'Settings updated successfully');
    }

    public function testEmailConfig(TestEmailConfigRequest $request): JsonResponse
    {
        $result = $this->service()->testEmailConfig($request->validated('recipient_email'));

        if ($result['test_result'] === 'failed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Email configuration test failed',
                'data' => $result,
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Email configuration test successful',
            'data' => $result,
        ]);
    }

    // ─── SMS Config ───────────────────────────────────────────────────────────

    public function getSmsConfig(): JsonResponse
    {
        $setting = $this->service()->getSmsConfig();
        return $this->success(new SmsConfigResource($setting), 'sms-config');
    }

    public function updateSmsConfig(UpdateSmsConfigRequest $request): JsonResponse
    {
        $setting = $this->service()->updateSmsConfig($request->validated());
        return $this->success(new SmsConfigResource($setting), 'sms-config', 'Settings updated successfully');
    }

    public function testSmsConfig(TestSmsConfigRequest $request): JsonResponse
    {
        $result = $this->service()->testSmsConfig($request->validated('recipient_phone'));

        if ($result['test_result'] === 'failed') {
            return response()->json([
                'status' => 'error',
                'message' => 'SMS configuration test failed',
                'data' => $result,
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'SMS configuration test successful',
            'data' => $result,
        ]);
    }

    // ─── Billing ──────────────────────────────────────────────────────────────

    public function getBilling(): JsonResponse
    {
        $setting = $this->service()->getBilling();
        return $this->success(new BillingSettingsResource((object) $setting), 'billing');
    }

    public function updateBilling(UpdateBillingSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateBilling($request->validated());
        return $this->success(new BillingSettingsResource($setting), 'billing', 'Settings updated successfully');
    }

    // ─── Clinical ─────────────────────────────────────────────────────────────

    public function getClinical(): JsonResponse
    {
        $setting = $this->service()->getClinical();
        return $this->success(new ClinicalSettingsResource((object) $setting), 'clinical');
    }

    public function updateClinical(UpdateClinicalSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateClinical($request->validated());
        return $this->success(new ClinicalSettingsResource($setting), 'clinical', 'Settings updated successfully');
    }

    // ─── Appointments ─────────────────────────────────────────────────────────

    public function getAppointments(): JsonResponse
    {
        $setting = $this->service()->getAppointments();
        return $this->success(new AppointmentSettingsResource((object) $setting), 'appointments');
    }

    public function updateAppointments(UpdateAppointmentSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateAppointments($request->validated());
        return $this->success(new AppointmentSettingsResource($setting), 'appointments', 'Settings updated successfully');
    }

    // ─── IPD ──────────────────────────────────────────────────────────────────

    public function getIpd(): JsonResponse
    {
        $setting = $this->service()->getIpd();
        return $this->success(new IpdSettingsResource((object) $setting), 'ipd');
    }

    public function updateIpd(UpdateIpdSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateIpd($request->validated());
        return $this->success(new IpdSettingsResource($setting), 'ipd', 'Settings updated successfully');
    }

    // ─── Pharmacy ─────────────────────────────────────────────────────────────

    public function getPharmacy(): JsonResponse
    {
        $setting = $this->service()->getPharmacy();
        return $this->success(new PharmacySettingsResource((object) $setting), 'pharmacy');
    }

    public function updatePharmacy(UpdatePharmacySettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updatePharmacy($request->validated());
        return $this->success(new PharmacySettingsResource($setting), 'pharmacy', 'Settings updated successfully');
    }

    // ─── Lab ──────────────────────────────────────────────────────────────────

    public function getLab(): JsonResponse
    {
        $setting = $this->service()->getLab();
        return $this->success(new LabSettingsResource((object) $setting), 'lab');
    }

    public function updateLab(UpdateLabSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateLab($request->validated());
        return $this->success(new LabSettingsResource($setting), 'lab', 'Settings updated successfully');
    }

    // ─── Integrations ─────────────────────────────────────────────────────────

    public function getIntegrations(): JsonResponse
    {
        $setting = $this->service()->getIntegrations();
        return $this->success(new IntegrationSettingsResource((object) $setting), 'integrations');
    }

    public function updateIntegrations(UpdateIntegrationSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateIntegrations($request->validated());
        return $this->success(new IntegrationSettingsResource($setting), 'integrations', 'Settings updated successfully');
    }

    // ─── Security ─────────────────────────────────────────────────────────────

    public function getSecurity(): JsonResponse
    {
        $setting = $this->service()->getSecurity();
        return $this->success(new SecuritySettingsResource((object) $setting), 'security');
    }

    public function updateSecurity(UpdateSecuritySettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateSecurity($request->validated());
        return $this->success(new SecuritySettingsResource($setting), 'security', 'Settings updated successfully');
    }

    // ─── Templates ────────────────────────────────────────────────────────────

    public function getTemplates(): JsonResponse
    {
        $setting = $this->service()->getTemplates();
        return $this->success(new TemplateSettingsResource((object) $setting), 'templates');
    }

    public function updateTemplates(UpdateTemplateSettingsRequest $request): JsonResponse
    {
        $setting = $this->service()->updateTemplates($request->validated());
        return $this->success(new TemplateSettingsResource($setting), 'templates', 'Settings updated successfully');
    }

    // ─── Audit Logs ───────────────────────────────────────────────────────────

    public function getAuditLogs(Request $request): JsonResponse
    {
        $paginator = $this->service()->getAuditLogs($request->only(['section', 'user_id', 'from', 'to', 'per_page']));

        return response()->json([
            'status' => 'success',
            'message' => 'Audit logs retrieved successfully',
            'data' => AuditLogResource::collection($paginator),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
