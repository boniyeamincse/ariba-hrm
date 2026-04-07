<?php

use App\Http\Controllers\Api\Admin\TenantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Clinical\BillingController;
use App\Http\Controllers\Api\Clinical\BloodBankController;
use App\Http\Controllers\Api\Clinical\ConsultationController;
use App\Http\Controllers\Api\Clinical\DischargeController;
use App\Http\Controllers\Api\Clinical\EmergencyController;
use App\Http\Controllers\Api\Clinical\HrController;
use App\Http\Controllers\Api\Clinical\IpdController;
use App\Http\Controllers\Api\Clinical\InsuranceController;
use App\Http\Controllers\Api\Clinical\InventoryController;
use App\Http\Controllers\Api\Clinical\InvestigationOrderController;
use App\Http\Controllers\Api\Clinical\LabController;
use App\Http\Controllers\Api\Clinical\MortuaryController;
use App\Http\Controllers\Api\Clinical\OpdController;
use App\Http\Controllers\Api\Clinical\AppointmentController;
use App\Http\Controllers\Api\Clinical\PatientController;
use App\Http\Controllers\Api\Clinical\PatientMedicalHistoryController;
use App\Http\Controllers\Api\Clinical\PharmacyController;
use App\Http\Controllers\Api\Clinical\PrescriptionController;
use App\Http\Controllers\Api\Clinical\PrescriptionItemController;
use App\Http\Controllers\Api\Clinical\ReferralController;
use App\Http\Controllers\Api\Clinical\VisitController;
use App\Http\Controllers\Api\Clinical\OpdQueueController;
use App\Http\Controllers\Api\Clinical\VitalsController;
use App\Http\Controllers\Api\V1\Auth\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\Staff\StaffController as StaffV1Controller;
use App\Http\Controllers\Api\V1\Settings\SettingsController as SettingsV1Controller;
use App\Http\Controllers\Api\V1\Rbac\RbacController;
use App\Http\Controllers\Api\RoleDashboardController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'backend-api',
    ]);
});

Route::prefix('auth')->group(function (): void {
    Route::post('/bootstrap-super-admin', [AuthController::class, 'bootstrapSuperAdmin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/2fa/verify', [AuthController::class, 'verifyTwoFactor']);
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/sessions', [AuthController::class, 'sessions']);
    Route::delete('/sessions', [AuthController::class, 'revokeAllSessions']);
    Route::delete('/sessions/{tokenId}', [AuthController::class, 'revokeSession']);
    Route::post('/2fa/setup', [AuthController::class, 'setupTwoFactor']);
    Route::post('/2fa/enable', [AuthController::class, 'enableTwoFactor']);
    Route::delete('/2fa', [AuthController::class, 'disableTwoFactor']);
});

Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
    Route::middleware('permission:dashboard.view')->group(function (): void {
        Route::get('/menus', [\App\Http\Controllers\Api\MenuController::class, 'index']);
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
        Route::get('/dashboard/overview', [RoleDashboardController::class, 'overview']);
        Route::get('/dashboard/super-admin/panel', [RoleDashboardController::class, 'superAdminPanel']);
        Route::get('/dashboard/super-admin/menu', [RoleDashboardController::class, 'superAdminMenu']);
        Route::get('/dashboard/widgets', [RoleDashboardController::class, 'widgets']);
        Route::get('/dashboard/menu', [RoleDashboardController::class, 'menu']);
    });

    Route::prefix('users')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->middleware('permission:users.view');
        Route::post('/', [UserManagementController::class, 'store'])->middleware('permission:users.manage');
        Route::patch('/{user}', [UserManagementController::class, 'update'])->middleware('permission:users.manage');
    });

    Route::get('/reports/summary', [RoleDashboardController::class, 'reportsSummary'])
        ->middleware('permission:reports.view');
    
    Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);
});

Route::middleware(['tenant'])->group(function (): void {
    Route::get('/tenant-context', function () {
        return response()->json([
            'tenant' => request()->attributes->get('tenant'),
            'tenant_id' => request()->attributes->get('tenant_id'),
        ]);
    });
});

Route::middleware(['auth:sanctum', 'audit', 'permission:super-admin.manage-tenants'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
        Route::get('/tenants/{tenant}', [TenantController::class, 'show']);
        Route::patch('/tenants/{tenant}', [TenantController::class, 'update']);
        Route::patch('/tenants/{tenant}/metadata', [TenantController::class, 'updateMetadata']);
        Route::patch('/tenants/{tenant}/status', [TenantController::class, 'updateStatus']);
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy']);
    });

Route::middleware(['auth:sanctum', 'audit', 'permission:super-admin.manage-tenants'])
    ->group(function (): void {
        // Frontend compatibility aliases for tenant management.
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
        Route::get('/tenants/{tenant}', [TenantController::class, 'show']);
        Route::patch('/tenants/{tenant}', [TenantController::class, 'update']);
        Route::patch('/tenants/{tenant}/metadata', [TenantController::class, 'updateMetadata']);
        Route::patch('/tenants/{tenant}/status', [TenantController::class, 'updateStatus']);
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy']);
    });

Route::middleware(['auth:sanctum', 'tenant', 'audit'])
    ->prefix('clinical')
    ->group(function (): void {
        Route::get('/patients', [PatientController::class, 'index'])->middleware('permission:patient.view');
        Route::post('/patients', [PatientController::class, 'store'])->middleware('permission:patient.create');
        Route::get('/patients/{patient}', [PatientController::class, 'show'])->middleware('permission:patient.view');
        Route::patch('/patients/{patient}', [PatientController::class, 'update'])->middleware('permission:patient.update');
        Route::post('/patients/{patient}/photo', [PatientController::class, 'uploadPhoto'])->middleware('permission:patient.update');

        Route::get('/patients/{patient}/history', [PatientMedicalHistoryController::class, 'show'])->middleware('permission:patient.view');
        Route::patch('/patients/{patient}/history', [PatientMedicalHistoryController::class, 'update'])->middleware('permission:patient.update');

        Route::get('/patients/{patient}/visits', [VisitController::class, 'index'])->middleware('permission:patient.view');
        Route::post('/patients/{patient}/visits', [VisitController::class, 'store'])->middleware('permission:patient.update');

        Route::get('/opd/queue', [OpdController::class, 'queueList']);
        Route::post('/opd/queue', [OpdController::class, 'enqueue']);
        Route::post('/opd/consultations', [OpdController::class, 'consult']);
        Route::post('/opd/consultations/{consultation}/prescription', [OpdController::class, 'prescribe']);
        Route::post('/opd/consultations/{consultation}/investigations', [OpdController::class, 'orderInvestigations']);

        Route::prefix('opd')->group(function (): void {
            Route::get('/appointments/slots', [AppointmentController::class, 'listSlots'])->middleware('permission:appointment.view');
            Route::post('/appointments/book', [AppointmentController::class, 'book'])->middleware('permission:appointment.manage');
            Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->middleware('permission:appointment.manage');
            Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->middleware('permission:appointment.manage');

            Route::post('/queue/tokens', [OpdQueueController::class, 'generateToken'])->middleware('permission:appointment.manage');
            Route::get('/queue/state', [OpdQueueController::class, 'state'])->middleware('permission:appointment.view');
            Route::post('/queue/call-next', [OpdQueueController::class, 'callNext'])->middleware('permission:appointment.manage');
            Route::post('/queue/{opdQueue}/skip', [OpdQueueController::class, 'skip'])->middleware('permission:appointment.manage');

            Route::post('/vitals', [VitalsController::class, 'store'])->middleware('permission:consultation.create');

            Route::post('/consultations', [ConsultationController::class, 'store'])->middleware('permission:consultation.create');
            Route::get('/icd10/search', [ConsultationController::class, 'searchIcd10'])->middleware('permission:consultation.create');

            Route::post('/consultations/{consultation}/prescriptions', [PrescriptionController::class, 'store'])->middleware('permission:prescription.create');
            Route::post('/prescriptions/{prescription}/items', [PrescriptionItemController::class, 'store'])->middleware('permission:prescription.create');
            Route::get('/prescriptions/{prescription}/pdf-url', [PrescriptionController::class, 'pdfUrl'])->middleware('permission:prescription.create');

            Route::post('/consultations/{consultation}/sick-leave-certificate', [ConsultationController::class, 'createSickLeaveCertificate'])->middleware('permission:consultation.create');
            Route::get('/sick-leave-certificates/{certificate}/pdf-url', [ConsultationController::class, 'sickLeaveCertificateUrl'])->middleware('permission:consultation.create');

            Route::post('/consultations/{consultation}/investigations', [InvestigationOrderController::class, 'store'])->middleware('permission:investigation.create');

            Route::post('/referrals', [ReferralController::class, 'store'])->middleware('permission:consultation.create');
            Route::get('/referrals/{referral}', [ReferralController::class, 'show'])->middleware('permission:consultation.create');
            Route::post('/referrals/{referral}/letter', [ReferralController::class, 'generateLetter'])->middleware('permission:consultation.create');
        });

        Route::get('/ipd/beds', [IpdController::class, 'bedAvailability']);
        Route::post('/ipd/admissions', [IpdController::class, 'admit']);
        Route::post('/ipd/admissions/{admission}/ward-rounds', [IpdController::class, 'addWardRound']);
        Route::post('/ipd/admissions/{admission}/nursing-notes', [IpdController::class, 'addNursingNote']);
        Route::post('/ipd/admissions/{admission}/medications', [IpdController::class, 'addMedicationAdministration']);

        Route::get('/emergency/triage', [EmergencyController::class, 'index']);
        Route::post('/emergency/triage', [EmergencyController::class, 'triage']);

        Route::get('/pharmacy/drugs', [PharmacyController::class, 'drugs'])->middleware('permission:pharmacy.view');
        Route::post('/pharmacy/drugs', [PharmacyController::class, 'storeDrug'])->middleware('permission:pharmacy.manage');
        Route::post('/pharmacy/drugs/{drug}/batches', [PharmacyController::class, 'addBatch'])->middleware('permission:pharmacy.manage');
        Route::post('/pharmacy/dispense', [PharmacyController::class, 'dispense'])->middleware('permission:pharmacy.manage');

        Route::get('/lab/tests', [LabController::class, 'tests'])->middleware('permission:lab.view');
        Route::post('/lab/tests', [LabController::class, 'storeTest'])->middleware('permission:lab.manage');
        Route::post('/lab/samples', [LabController::class, 'collectSample'])->middleware('permission:lab.manage');
        Route::post('/lab/orders', [LabController::class, 'order'])->middleware('permission:lab.manage');
        Route::post('/lab/orders/{order}/results', [LabController::class, 'enterResult'])->middleware('permission:lab.manage');
        Route::post('/lab/results/{result}/validate', [LabController::class, 'validateResult'])->middleware('permission:lab.manage');
        Route::get('/lab/results/{result}/report', [LabController::class, 'report'])->middleware('permission:lab.view');

        Route::get('/billing/charges', [BillingController::class, 'charges'])->middleware('permission:billing.view');
        Route::post('/billing/charges', [BillingController::class, 'storeCharge'])->middleware('permission:billing.manage');
        Route::post('/billing/invoices', [BillingController::class, 'createInvoice'])->middleware('permission:billing.manage');
        Route::post('/billing/invoices/{invoice}/payments', [BillingController::class, 'addPayment'])->middleware('permission:billing.manage');
        Route::post('/billing/invoices/{invoice}/discount-approve', [BillingController::class, 'approveDiscount'])->middleware('permission:billing.manage');

        Route::post('/ipd/admissions/{admission}/discharge-clearance', [DischargeController::class, 'clear'])->middleware('permission:billing.manage');

        Route::get('/appointments/slots', [AppointmentController::class, 'slots'])->middleware('permission:appointment.view');
        Route::post('/appointments/slots', [AppointmentController::class, 'createSlot'])->middleware('permission:appointment.manage');
        Route::post('/appointments/book', [AppointmentController::class, 'book'])->middleware('permission:appointment.manage');
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->middleware('permission:appointment.manage');
        Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->middleware('permission:appointment.manage');
        Route::post('/appointments/{appointment}/telemedicine', [AppointmentController::class, 'createTelemedicineSession'])->middleware('permission:appointment.manage');

        Route::get('/insurance/providers', [InsuranceController::class, 'providers']);
        Route::post('/insurance/providers', [InsuranceController::class, 'createProvider']);
        Route::post('/insurance/policies', [InsuranceController::class, 'createPolicy']);
        Route::post('/insurance/claims', [InsuranceController::class, 'submitClaim']);
        Route::post('/insurance/claims/{claim}/approve', [InsuranceController::class, 'approveClaim']);

        Route::get('/inventory/items', [InventoryController::class, 'items'])->middleware('permission:inventory.view');
        Route::post('/inventory/items', [InventoryController::class, 'createItem'])->middleware('permission:inventory.manage');
        Route::post('/inventory/procurement-orders', [InventoryController::class, 'createProcurementOrder'])->middleware('permission:inventory.manage');

        Route::get('/hr/staff', [HrController::class, 'staff'])->middleware('permission:hr.view');
        Route::post('/hr/staff', [HrController::class, 'createStaff'])->middleware('permission:hr.manage');
        Route::post('/hr/payroll/runs', [HrController::class, 'runPayroll'])->middleware('permission:hr.manage');

        Route::get('/blood-bank/stock', [BloodBankController::class, 'stock']);
        Route::post('/blood-bank/donations', [BloodBankController::class, 'addDonation']);
        Route::post('/blood-bank/transfusions', [BloodBankController::class, 'transfuse']);

        Route::get('/mortuary/records', [MortuaryController::class, 'index']);
        Route::post('/mortuary/records', [MortuaryController::class, 'create']);
        Route::post('/mortuary/records/{record}/release', [MortuaryController::class, 'release']);
    });

Route::middleware(['auth:sanctum', 'tenant', 'audit'])
    ->prefix('v1/settings')
    ->group(function (): void {
        // General
        Route::get('/general', [SettingsV1Controller::class, 'getGeneral'])->middleware('permission:settings.read');
        Route::put('/general', [SettingsV1Controller::class, 'updateGeneral'])->middleware('permission:settings.update');

        // Branding
        Route::get('/branding', [SettingsV1Controller::class, 'getBranding'])->middleware('permission:settings.read');
        Route::put('/branding', [SettingsV1Controller::class, 'updateBranding'])->middleware('permission:settings.branding.update');

        // Localization
        Route::get('/localization', [SettingsV1Controller::class, 'getLocalization'])->middleware('permission:settings.read');
        Route::put('/localization', [SettingsV1Controller::class, 'updateLocalization'])->middleware('permission:settings.update');

        // Notifications
        Route::get('/notifications', [SettingsV1Controller::class, 'getNotifications'])->middleware('permission:settings.read');
        Route::put('/notifications', [SettingsV1Controller::class, 'updateNotifications'])->middleware('permission:settings.notification.update');

        // Email Config
        Route::get('/email-config', [SettingsV1Controller::class, 'getEmailConfig'])->middleware('permission:settings.read');
        Route::put('/email-config', [SettingsV1Controller::class, 'updateEmailConfig'])->middleware('permission:settings.update');
        Route::post('/email-config/test', [SettingsV1Controller::class, 'testEmailConfig'])->middleware('permission:settings.update');

        // SMS Config
        Route::get('/sms-config', [SettingsV1Controller::class, 'getSmsConfig'])->middleware('permission:settings.read');
        Route::put('/sms-config', [SettingsV1Controller::class, 'updateSmsConfig'])->middleware('permission:settings.update');
        Route::post('/sms-config/test', [SettingsV1Controller::class, 'testSmsConfig'])->middleware('permission:settings.update');

        // Billing
        Route::get('/billing', [SettingsV1Controller::class, 'getBilling'])->middleware('permission:settings.read');
        Route::put('/billing', [SettingsV1Controller::class, 'updateBilling'])->middleware('permission:settings.billing.update');

        // Clinical
        Route::get('/clinical', [SettingsV1Controller::class, 'getClinical'])->middleware('permission:settings.read');
        Route::put('/clinical', [SettingsV1Controller::class, 'updateClinical'])->middleware('permission:settings.clinical.update');

        // Appointments
        Route::get('/appointments', [SettingsV1Controller::class, 'getAppointments'])->middleware('permission:settings.read');
        Route::put('/appointments', [SettingsV1Controller::class, 'updateAppointments'])->middleware('permission:settings.update');

        // IPD
        Route::get('/ipd', [SettingsV1Controller::class, 'getIpd'])->middleware('permission:settings.read');
        Route::put('/ipd', [SettingsV1Controller::class, 'updateIpd'])->middleware('permission:settings.update');

        // Pharmacy
        Route::get('/pharmacy', [SettingsV1Controller::class, 'getPharmacy'])->middleware('permission:settings.read');
        Route::put('/pharmacy', [SettingsV1Controller::class, 'updatePharmacy'])->middleware('permission:settings.update');

        // Lab
        Route::get('/lab', [SettingsV1Controller::class, 'getLab'])->middleware('permission:settings.read');
        Route::put('/lab', [SettingsV1Controller::class, 'updateLab'])->middleware('permission:settings.update');

        // Integrations
        Route::get('/integrations', [SettingsV1Controller::class, 'getIntegrations'])->middleware('permission:settings.read');
        Route::put('/integrations', [SettingsV1Controller::class, 'updateIntegrations'])->middleware('permission:settings.integration.update');

        // Security
        Route::get('/security', [SettingsV1Controller::class, 'getSecurity'])->middleware('permission:settings.read');
        Route::put('/security', [SettingsV1Controller::class, 'updateSecurity'])->middleware('permission:settings.security.update');

        // Templates
        Route::get('/templates', [SettingsV1Controller::class, 'getTemplates'])->middleware('permission:settings.read');
        Route::put('/templates', [SettingsV1Controller::class, 'updateTemplates'])->middleware('permission:settings.update');

        // Audit Logs
        Route::get('/audit-logs', [SettingsV1Controller::class, 'getAuditLogs'])->middleware('permission:settings.audit.read');
    });

// Staff Module Routes (v1)
Route::middleware(['auth:sanctum', 'tenant', 'audit'])
    ->prefix('v1/staff')
    ->group(function (): void {
        // Staff core
        Route::get('/', [StaffV1Controller::class, 'index'])->middleware('permission:staff.view');
        Route::post('/', [StaffV1Controller::class, 'store'])->middleware('permission:staff.create');
        Route::get('/options', [StaffV1Controller::class, 'options'])->middleware('permission:staff.view');
        Route::get('/{id}', [StaffV1Controller::class, 'show'])->middleware('permission:staff.view');
        Route::put('/{id}', [StaffV1Controller::class, 'update'])->middleware('permission:staff.update');
        Route::delete('/{id}', [StaffV1Controller::class, 'destroy'])->middleware('permission:staff.delete');
        Route::patch('/{id}/status', [StaffV1Controller::class, 'updateStatus'])->middleware('permission:staff.status.update');
        Route::get('/{id}/summary', [StaffV1Controller::class, 'summary'])->middleware('permission:staff.view');

        // Assignments
        Route::post('/{id}/assign-branch', [StaffV1Controller::class, 'assignBranch'])->middleware('permission:staff.assign.branch');
        Route::post('/{id}/assign-facility', [StaffV1Controller::class, 'assignFacility'])->middleware('permission:staff.assign.facility');
        Route::post('/{id}/assign-department', [StaffV1Controller::class, 'assignDepartment'])->middleware('permission:staff.assign.department');
        Route::post('/{id}/assign-manager', [StaffV1Controller::class, 'assignManager'])->middleware('permission:staff.assign.manager');
        Route::post('/{id}/assign-user-account', [StaffV1Controller::class, 'assignUserAccount'])->middleware('permission:staff.assign.user');

        Route::get('/{id}/branch', [StaffV1Controller::class, 'branch'])->middleware('permission:staff.view');
        Route::get('/{id}/facility', [StaffV1Controller::class, 'facility'])->middleware('permission:staff.view');
        Route::get('/{id}/department', [StaffV1Controller::class, 'department'])->middleware('permission:staff.view');
        Route::get('/{id}/manager', [StaffV1Controller::class, 'manager'])->middleware('permission:staff.view');
        Route::get('/{id}/user-account', [StaffV1Controller::class, 'userAccount'])->middleware('permission:staff.view');

        // Employment lifecycle
        Route::post('/{id}/confirm', [StaffV1Controller::class, 'confirm'])->middleware('permission:staff.status.update');
        Route::post('/{id}/probation', [StaffV1Controller::class, 'probation'])->middleware('permission:staff.status.update');
        Route::post('/{id}/suspend', [StaffV1Controller::class, 'suspend'])->middleware('permission:staff.status.update');
        Route::post('/{id}/terminate', [StaffV1Controller::class, 'terminate'])->middleware('permission:staff.status.update');
        Route::post('/{id}/resign', [StaffV1Controller::class, 'resign'])->middleware('permission:staff.status.update');
        Route::post('/{id}/reactivate', [StaffV1Controller::class, 'reactivate'])->middleware('permission:staff.status.update');
        Route::get('/{id}/employment-history', [StaffV1Controller::class, 'employmentHistory'])->middleware('permission:staff.employment-history.view');

        // Licenses
        Route::get('/{id}/licenses', [StaffV1Controller::class, 'licenses'])->middleware('permission:staff.view');
        Route::post('/{id}/licenses', [StaffV1Controller::class, 'storeLicense'])->middleware('permission:staff.license.manage');
        Route::put('/{id}/licenses/{licenseId}', [StaffV1Controller::class, 'updateLicense'])->middleware('permission:staff.license.manage');
        Route::delete('/{id}/licenses/{licenseId}', [StaffV1Controller::class, 'destroyLicense'])->middleware('permission:staff.license.manage');

        // Emergency contacts
        Route::get('/{id}/emergency-contacts', [StaffV1Controller::class, 'emergencyContacts'])->middleware('permission:staff.view');
        Route::post('/{id}/emergency-contacts', [StaffV1Controller::class, 'storeEmergencyContact'])->middleware('permission:staff.emergency-contact.manage');
        Route::put('/{id}/emergency-contacts/{contactId}', [StaffV1Controller::class, 'updateEmergencyContact'])->middleware('permission:staff.emergency-contact.manage');
        Route::delete('/{id}/emergency-contacts/{contactId}', [StaffV1Controller::class, 'destroyEmergencyContact'])->middleware('permission:staff.emergency-contact.manage');

        // Documents
        Route::get('/{id}/documents', [StaffV1Controller::class, 'documents'])->middleware('permission:staff.view');
        Route::post('/{id}/documents', [StaffV1Controller::class, 'storeDocument'])->middleware('permission:staff.document.manage');
        Route::delete('/{id}/documents/{documentId}', [StaffV1Controller::class, 'destroyDocument'])->middleware('permission:staff.document.manage');
    });

Route::prefix('v1/auth')->group(function (): void {
    Route::post('/bootstrap-super-admin', [AuthController::class, 'bootstrapSuperAdmin']);
    Route::post('/login', [V1AuthController::class, 'login']);
    Route::post('/forgot-password', [V1AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [V1AuthController::class, 'resetPassword']);
    Route::post('/2fa/verify', [V1AuthController::class, 'verifyTwoFactor']);
    Route::post('/register-tenant-admin', [V1AuthController::class, 'registerTenantAdmin']);
    Route::post('/refresh-token', [V1AuthController::class, 'refreshToken']);
    Route::post('/resend-otp', [V1AuthController::class, 'resendOtp']);
    Route::post('/verify-email', [V1AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification-email', [V1AuthController::class, 'resendVerificationEmail']);
});

Route::middleware(['auth:sanctum', 'audit'])->prefix('v1/auth')->group(function (): void {
    Route::get('/me', [V1AuthController::class, 'me']);
    Route::post('/logout', [V1AuthController::class, 'logout']);
    Route::post('/logout-all-devices', [V1AuthController::class, 'logoutAllDevices']);
    Route::post('/change-password', [V1AuthController::class, 'changePassword']);
    Route::get('/sessions', [V1AuthController::class, 'sessions'])->middleware('permission:auth.session.manage');
    Route::delete('/sessions/{id}', [V1AuthController::class, 'revokeSession'])->middleware('permission:auth.session.manage');
    Route::post('/2fa/enable', [V1AuthController::class, 'enableTwoFactor'])->middleware('permission:auth.2fa.manage');
    Route::post('/2fa/disable', [V1AuthController::class, 'disableTwoFactor'])->middleware('permission:auth.2fa.manage');
    Route::get('/2fa/status', [V1AuthController::class, 'twoFactorStatus'])->middleware('permission:auth.2fa.manage');
});

// RBAC Module Routes (v1)
Route::middleware(['auth:sanctum', 'tenant', 'audit'])
    ->prefix('v1/rbac')
    ->group(function (): void {
        // Roles Management
        Route::get('/roles', [RbacController::class, 'indexRoles'])->middleware('permission:rbac:view_roles');
        Route::post('/roles', [RbacController::class, 'storeRole'])->middleware('permission:rbac:create_role');
        Route::get('/roles/{id}', [RbacController::class, 'showRole'])->middleware('permission:rbac:view_roles');
        Route::patch('/roles/{id}', [RbacController::class, 'updateRole'])->middleware('permission:rbac:update_role');
        Route::delete('/roles/{id}', [RbacController::class, 'deleteRole'])->middleware('permission:rbac:delete_role');

        // Permissions Management
        Route::get('/permissions', [RbacController::class, 'indexPermissions'])->middleware('permission:rbac:view_permissions');
        Route::post('/permissions', [RbacController::class, 'storePermission'])->middleware('permission:rbac:create_permission');
        Route::get('/permissions/{id}', [RbacController::class, 'showPermission'])->middleware('permission:rbac:view_permissions');
        Route::patch('/permissions/{id}', [RbacController::class, 'updatePermission'])->middleware('permission:rbac:update_permission');
        Route::delete('/permissions/{id}', [RbacController::class, 'deletePermission'])->middleware('permission:rbac:delete_permission');

        // Role-Permission Mapping
        Route::put('/roles/{id}/permissions', [RbacController::class, 'syncRolePermissions'])->middleware('permission:rbac:sync_permissions');

        // Permission Groups
        Route::get('/permission-groups', [RbacController::class, 'indexPermissionGroups']);
        Route::post('/permission-groups', [RbacController::class, 'storePermissionGroup'])->middleware('permission:rbac:manage_groups');

        // User-Role Assignment
        Route::post('/users/{userId}/roles', [RbacController::class, 'assignRolesToUser'])->middleware('permission:rbac:assign_role');
        Route::delete('/users/{userId}/roles/{roleId}', [RbacController::class, 'removeRoleFromUser'])->middleware('permission:rbac:assign_role');

        // RBAC Matrix Dashboard
        Route::get('/matrix', [RbacController::class, 'getMatrix'])->middleware('permission:rbac:view_matrix');
    });
