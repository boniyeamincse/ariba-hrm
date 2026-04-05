<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\InsuranceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function providers(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $data = InsuranceProvider::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->get();

        return response()->json(['data' => $data]);
    }

    public function createProvider(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40'],
            'contact_info' => ['nullable', 'string'],
        ]);

        $provider = InsuranceProvider::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'name' => $data['name'],
            'code' => $data['code'],
            'contact_info' => $data['contact_info'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['provider' => $provider], 201);
    }

    public function createPolicy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'insurance_provider_id' => ['required', 'integer', 'exists:insurance_providers,id'],
            'policy_no' => ['required', 'string', 'max:100'],
            'coverage_limit' => ['required', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
        ]);

        $policy = InsurancePolicy::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'insurance_provider_id' => $data['insurance_provider_id'],
            'policy_no' => $data['policy_no'],
            'coverage_limit' => $data['coverage_limit'],
            'used_amount' => 0,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'status' => 'active',
        ]);

        return response()->json(['policy' => $policy], 201);
    }

    public function submitClaim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'insurance_policy_id' => ['required', 'integer', 'exists:insurance_policies,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'claim_amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $claim = InsuranceClaim::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'insurance_policy_id' => $data['insurance_policy_id'],
            'invoice_id' => $data['invoice_id'] ?? null,
            'claim_no' => 'CLM-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'claim_amount' => $data['claim_amount'],
            'approved_amount' => 0,
            'status' => 'submitted',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['claim' => $claim], 201);
    }

    public function approveClaim(Request $request, InsuranceClaim $claim): JsonResponse
    {
        $data = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $claim->update([
            'approved_amount' => $data['approved_amount'],
            'status' => 'approved',
        ]);

        return response()->json(['claim' => $claim]);
    }
}
