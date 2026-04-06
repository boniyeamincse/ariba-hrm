<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientMedicalHistoryController extends Controller
{
    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        return response()->json([
            'history' => $patient->history,
        ]);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        $data = $request->validate([
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'surgical_history' => ['nullable', 'string'],
            'family_history' => ['nullable', 'string'],
            'immunization_records' => ['nullable', 'string'],
        ]);

        $history = $patient->history ?: new PatientHistory(['patient_id' => $patient->id]);
        $history->fill($data);
        $history->save();

        return response()->json([
            'message' => 'Patient medical history updated successfully.',
            'history' => $history,
        ]);
    }

    private function ensureTenantAccess(Request $request, Patient $patient): void
    {
        $tenantId = $request->attributes->get('tenant_id');

        if ($tenantId === null) {
            $tenantId = $request->user()?->tenant_id;
        }

        if ($tenantId && (int) $patient->tenant_id !== (int) $tenantId) {
            abort(404);
        }
    }
}
