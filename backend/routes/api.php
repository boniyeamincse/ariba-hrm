<?php

use App\Http\Controllers\Api\Admin\TenantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Clinical\BillingController;
use App\Http\Controllers\Api\Clinical\BloodBankController;
use App\Http\Controllers\Api\Clinical\DischargeController;
use App\Http\Controllers\Api\Clinical\EmergencyController;
use App\Http\Controllers\Api\Clinical\HrController;
use App\Http\Controllers\Api\Clinical\IpdController;
use App\Http\Controllers\Api\Clinical\InsuranceController;
use App\Http\Controllers\Api\Clinical\InventoryController;
use App\Http\Controllers\Api\Clinical\LabController;
use App\Http\Controllers\Api\Clinical\MortuaryController;
use App\Http\Controllers\Api\Clinical\OpdController;
use App\Http\Controllers\Api\Clinical\AppointmentController;
use App\Http\Controllers\Api\Clinical\PatientController;
use App\Http\Controllers\Api\Clinical\PharmacyController;
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
        Route::get('/patients', [PatientController::class, 'index']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
        Route::patch('/patients/{patient}/history', [PatientController::class, 'updateHistory']);
        Route::get('/patients/{patient}/timeline', [PatientController::class, 'timeline']);

        Route::get('/opd/queue', [OpdController::class, 'queueList']);
        Route::post('/opd/queue', [OpdController::class, 'enqueue']);
        Route::post('/opd/consultations', [OpdController::class, 'consult']);
        Route::post('/opd/consultations/{consultation}/prescription', [OpdController::class, 'prescribe']);
        Route::post('/opd/consultations/{consultation}/investigations', [OpdController::class, 'orderInvestigations']);

        Route::get('/ipd/beds', [IpdController::class, 'bedAvailability']);
        Route::post('/ipd/admissions', [IpdController::class, 'admit']);
        Route::post('/ipd/admissions/{admission}/ward-rounds', [IpdController::class, 'addWardRound']);
        Route::post('/ipd/admissions/{admission}/nursing-notes', [IpdController::class, 'addNursingNote']);
        Route::post('/ipd/admissions/{admission}/medications', [IpdController::class, 'addMedicationAdministration']);

        Route::get('/emergency/triage', [EmergencyController::class, 'index']);
        Route::post('/emergency/triage', [EmergencyController::class, 'triage']);

        Route::get('/pharmacy/drugs', [PharmacyController::class, 'drugs']);
        Route::post('/pharmacy/drugs', [PharmacyController::class, 'storeDrug']);
        Route::post('/pharmacy/drugs/{drug}/batches', [PharmacyController::class, 'addBatch']);
        Route::post('/pharmacy/dispense', [PharmacyController::class, 'dispense']);

        Route::get('/lab/tests', [LabController::class, 'tests']);
        Route::post('/lab/tests', [LabController::class, 'storeTest']);
        Route::post('/lab/samples', [LabController::class, 'collectSample']);
        Route::post('/lab/orders', [LabController::class, 'order']);
        Route::post('/lab/orders/{order}/results', [LabController::class, 'enterResult']);
        Route::post('/lab/results/{result}/validate', [LabController::class, 'validateResult']);
        Route::get('/lab/results/{result}/report', [LabController::class, 'report']);

        Route::get('/billing/charges', [BillingController::class, 'charges']);
        Route::post('/billing/charges', [BillingController::class, 'storeCharge']);
        Route::post('/billing/invoices', [BillingController::class, 'createInvoice']);
        Route::post('/billing/invoices/{invoice}/payments', [BillingController::class, 'addPayment']);
        Route::post('/billing/invoices/{invoice}/discount-approve', [BillingController::class, 'approveDiscount']);

        Route::post('/ipd/admissions/{admission}/discharge-clearance', [DischargeController::class, 'clear']);

        Route::get('/appointments/slots', [AppointmentController::class, 'slots']);
        Route::post('/appointments/slots', [AppointmentController::class, 'createSlot']);
        Route::post('/appointments/book', [AppointmentController::class, 'book']);
        Route::post('/appointments/{appointment}/telemedicine', [AppointmentController::class, 'createTelemedicineSession']);

        Route::get('/insurance/providers', [InsuranceController::class, 'providers']);
        Route::post('/insurance/providers', [InsuranceController::class, 'createProvider']);
        Route::post('/insurance/policies', [InsuranceController::class, 'createPolicy']);
        Route::post('/insurance/claims', [InsuranceController::class, 'submitClaim']);
        Route::post('/insurance/claims/{claim}/approve', [InsuranceController::class, 'approveClaim']);

        Route::get('/inventory/items', [InventoryController::class, 'items']);
        Route::post('/inventory/items', [InventoryController::class, 'createItem']);
        Route::post('/inventory/procurement-orders', [InventoryController::class, 'createProcurementOrder']);

        Route::get('/hr/staff', [HrController::class, 'staff']);
        Route::post('/hr/staff', [HrController::class, 'createStaff']);
        Route::post('/hr/payroll/runs', [HrController::class, 'runPayroll']);

        Route::get('/blood-bank/stock', [BloodBankController::class, 'stock']);
        Route::post('/blood-bank/donations', [BloodBankController::class, 'addDonation']);
        Route::post('/blood-bank/transfusions', [BloodBankController::class, 'transfuse']);

        Route::get('/mortuary/records', [MortuaryController::class, 'index']);
        Route::post('/mortuary/records', [MortuaryController::class, 'create']);
        Route::post('/mortuary/records/{record}/release', [MortuaryController::class, 'release']);
    });
