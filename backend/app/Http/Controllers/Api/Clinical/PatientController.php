<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientHistory;
use App\Services\UhidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $patients = Patient::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(20);

        return response()->json($patients);
    }

    public function store(Request $request, UhidService $uhidService): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $data['tenant_id'] = $request->attributes->get('tenant_id');
        $data['uhid'] = $uhidService->generate();

        $patient = Patient::create($data);

        PatientHistory::create([
            'patient_id' => $patient->id,
        ]);

        return response()->json([
            'message' => 'Patient registered successfully.',
            'patient' => $patient,
        ], 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        return response()->json([
            'patient' => $patient->load(['history', 'visits']),
        ]);
    }

    public function updateHistory(Request $request, Patient $patient): JsonResponse
    {
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
            'message' => 'Patient history updated.',
            'history' => $history,
        ]);
    }

    public function timeline(Patient $patient): JsonResponse
    {
        $timeline = $patient->visits()
            ->orderByDesc('visit_at')
            ->get();

        return response()->json([
            'patient_id' => $patient->id,
            'timeline' => $timeline,
        ]);
    }
}
