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
