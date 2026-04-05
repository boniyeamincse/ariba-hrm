<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\MortuaryRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MortuaryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = MortuaryRecord::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'deceased_name' => ['required', 'string', 'max:255'],
            'cause_of_death' => ['nullable', 'string', 'max:255'],
            'time_of_death' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = MortuaryRecord::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'] ?? null,
            'deceased_name' => $data['deceased_name'],
            'cause_of_death' => $data['cause_of_death'] ?? null,
            'time_of_death' => $data['time_of_death'],
            'status' => 'received',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['record' => $record], 201);
    }

    public function release(Request $request, MortuaryRecord $record): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $record->update([
            'status' => 'released',
            'notes' => $data['notes'] ?? $record->notes,
        ]);

        return response()->json(['record' => $record]);
    }
}
