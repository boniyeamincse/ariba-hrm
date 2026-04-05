<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\EmergencyTriage;
use App\Models\PatientVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function triage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'triage_level' => ['required', 'in:red,orange,yellow,green,blue'],
            'complaint' => ['nullable', 'string'],
            'vitals' => ['nullable', 'array'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');

        $visit = PatientVisit::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'visit_type' => 'emergency',
            'reference_no' => 'ER-'.now()->format('YmdHis').'-'.$data['patient_id'],
            'visit_at' => now(),
            'status' => 'active',
        ]);

        $triage = EmergencyTriage::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'patient_visit_id' => $visit->id,
            'triage_level' => $data['triage_level'],
            'complaint' => $data['complaint'] ?? null,
            'vitals' => $data['vitals'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Emergency triage recorded.',
            'triage' => $triage,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $items = EmergencyTriage::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->orderByRaw("FIELD(triage_level, 'red', 'orange', 'yellow', 'green', 'blue')")
            ->latest()
            ->get();

        return response()->json(['data' => $items]);
    }
}
