<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\IpdAdmission;
use App\Models\PatientVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpdController extends Controller
{
    public function admit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'bed_id' => ['required', 'integer', 'exists:beds,id'],
            'reason' => ['nullable', 'string'],
        ]);

        $bed = Bed::findOrFail($data['bed_id']);
        if ($bed->is_occupied) {
            return response()->json(['message' => 'Bed already occupied.'], 422);
        }

        $tenantId = $request->attributes->get('tenant_id');

        $visit = PatientVisit::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'visit_type' => 'ipd',
            'reference_no' => 'IPD-'.now()->format('Ymd').'-'.$data['patient_id'],
            'visit_at' => now(),
            'status' => 'active',
        ]);

        $admission = IpdAdmission::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'patient_visit_id' => $visit->id,
            'admitted_by' => $request->user()?->id,
            'bed_id' => $data['bed_id'],
            'reason' => $data['reason'] ?? null,
            'admitted_at' => now(),
            'status' => 'admitted',
        ]);

        $bed->update(['is_occupied' => true]);

        return response()->json([
            'message' => 'Patient admitted to IPD.',
            'admission' => $admission,
        ], 201);
    }

    public function addWardRound(Request $request, IpdAdmission $admission): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['required', 'string'],
            'rounded_at' => ['nullable', 'date'],
        ]);

        $round = $admission->wardRounds()->create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'doctor_id' => $request->user()?->id,
            'notes' => $data['notes'],
            'rounded_at' => $data['rounded_at'] ?? now(),
        ]);

        return response()->json(['round' => $round], 201);
    }

    public function addNursingNote(Request $request, IpdAdmission $admission): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['required', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $note = $admission->nursingNotes()->create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'nurse_id' => $request->user()?->id,
            'notes' => $data['notes'],
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        return response()->json(['note' => $note], 201);
    }

    public function addMedicationAdministration(Request $request, IpdAdmission $admission): JsonResponse
    {
        $data = $request->validate([
            'medicine_name' => ['required', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:100'],
            'route' => ['nullable', 'string', 'max:100'],
            'administered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $entry = $admission->medicationAdministrations()->create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'nurse_id' => $request->user()?->id,
            'medicine_name' => $data['medicine_name'],
            'dose' => $data['dose'] ?? null,
            'route' => $data['route'] ?? null,
            'administered_at' => $data['administered_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['entry' => $entry], 201);
    }

    public function bedAvailability(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $beds = Bed::query()
            ->with('ward')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('is_occupied')
            ->orderBy('ward_id')
            ->orderBy('bed_number')
            ->get();

        return response()->json(['data' => $beds]);
    }
}
