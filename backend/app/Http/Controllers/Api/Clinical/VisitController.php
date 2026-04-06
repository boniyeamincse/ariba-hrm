<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        $visits = $patient->visits()
            ->when($request->filled('visit_type'), fn (Builder $query) => $query->where('visit_type', (string) $request->string('visit_type')))
            ->orderByDesc('visit_at')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($visits);
    }

    public function store(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        $data = $request->validate([
            'visit_type' => ['required', 'in:opd,ipd,emergency'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'visit_at' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:30'],
            'summary' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        $visit = PatientVisit::create([
            'tenant_id' => $tenantId,
            'patient_id' => $patient->id,
            'visit_type' => $data['visit_type'],
            'reference_no' => $data['reference_no'] ?? null,
            'visit_at' => $data['visit_at'],
            'status' => $data['status'] ?? 'active',
            'summary' => $data['summary'] ?? null,
            'meta' => $data['meta'] ?? null,
        ]);

        return response()->json([
            'message' => 'Visit created successfully.',
            'visit' => $visit,
        ], 201);
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
