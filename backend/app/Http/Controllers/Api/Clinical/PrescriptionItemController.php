<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionItemController extends Controller
{
    public function store(Request $request, Prescription $prescription): JsonResponse
    {
        $data = $request->validate([
            'drug_id' => ['nullable', 'integer', 'exists:drugs,id'],
            'medicine_name' => ['required_without:drug_id', 'nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'frequency' => ['nullable', 'string', 'max:100'],
            'duration' => ['nullable', 'string', 'max:100'],
            'route' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $drug = ! empty($data['drug_id']) ? Drug::query()->find($data['drug_id']) : null;
        $medicineName = $data['medicine_name'] ?? ($drug?->brand_name ?: $drug?->generic_name ?: 'Unknown Drug');

        $item = $prescription->items()->create([
            'drug_id' => $data['drug_id'] ?? null,
            'medicine_name' => $medicineName,
            'dosage' => $data['dosage'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'duration' => $data['duration'] ?? null,
            'route' => $data['route'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Prescription item added.',
            'item' => $item,
        ], 201);
    }
}
