<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Vital;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitalsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'consultation_id' => ['nullable', 'integer', 'exists:consultations,id'],
            'bp_systolic' => ['nullable', 'integer', 'between:40,300'],
            'bp_diastolic' => ['nullable', 'integer', 'between:20,200'],
            'temperature_c' => ['nullable', 'numeric', 'between:25,45'],
            'pulse' => ['nullable', 'integer', 'between:20,250'],
            'spo2' => ['nullable', 'integer', 'between:30,100'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,500'],
            'height_cm' => ['nullable', 'numeric', 'between:20,300'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,80'],
            'pain_score' => ['nullable', 'integer', 'between:0,10'],
            'notes' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $heightCm = isset($data['height_cm']) ? (float) $data['height_cm'] : null;
        $weightKg = isset($data['weight_kg']) ? (float) $data['weight_kg'] : null;
        $bmi = null;

        if ($heightCm && $weightKg && $heightCm > 0) {
            $heightM = $heightCm / 100;
            $bmi = round($weightKg / ($heightM * $heightM), 2);
        }

        $vital = Vital::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'consultation_id' => $data['consultation_id'] ?? null,
            'recorded_by' => $request->user()?->id,
            'bp_systolic' => $data['bp_systolic'] ?? null,
            'bp_diastolic' => $data['bp_diastolic'] ?? null,
            'temperature_c' => $data['temperature_c'] ?? null,
            'pulse' => $data['pulse'] ?? null,
            'spo2' => $data['spo2'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'bmi' => $bmi,
            'respiratory_rate' => $data['respiratory_rate'] ?? null,
            'pain_score' => $data['pain_score'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Vitals recorded successfully.',
            'vital' => $vital,
        ], 201);
    }
}
