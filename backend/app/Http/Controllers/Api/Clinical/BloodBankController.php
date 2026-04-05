<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodProduct;
use App\Models\BloodTransfusion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BloodBankController extends Controller
{
    public function stock(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $data = BloodProduct::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('blood_group')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function addDonation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'donor_name' => ['required', 'string', 'max:255'],
            'blood_group' => ['required', 'string', 'max:5'],
            'units' => ['required', 'integer', 'min:1'],
            'donated_on' => ['nullable', 'date'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');

        $donation = BloodDonation::create([
            'tenant_id' => $tenantId,
            'donor_name' => $data['donor_name'],
            'blood_group' => strtoupper($data['blood_group']),
            'units' => $data['units'],
            'donated_on' => $data['donated_on'] ?? now()->toDateString(),
        ]);

        $product = BloodProduct::firstOrCreate(
            ['tenant_id' => $tenantId, 'blood_group' => strtoupper($data['blood_group'])],
            ['units_available' => 0]
        );
        $product->increment('units_available', $data['units']);

        return response()->json(['donation' => $donation, 'stock' => $product], 201);
    }

    public function transfuse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'blood_group' => ['required', 'string', 'max:5'],
            'units' => ['required', 'integer', 'min:1'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $group = strtoupper($data['blood_group']);

        $product = BloodProduct::query()
            ->where('tenant_id', $tenantId)
            ->where('blood_group', $group)
            ->first();

        if (! $product || $product->units_available < $data['units']) {
            return response()->json(['message' => 'Insufficient blood stock.'], 422);
        }

        $product->decrement('units_available', $data['units']);

        $transfusion = BloodTransfusion::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'blood_group' => $group,
            'units' => $data['units'],
            'transfused_at' => now(),
        ]);

        return response()->json(['transfusion' => $transfusion, 'stock' => $product]);
    }
}
