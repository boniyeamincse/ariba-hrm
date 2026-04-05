<?php

use App\Http\Controllers\Api\Admin\TenantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Clinical\EmergencyController;
use App\Http\Controllers\Api\Clinical\IpdController;
use App\Http\Controllers\Api\Clinical\OpdController;
use App\Http\Controllers\Api\Clinical\PatientController;
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
    });
