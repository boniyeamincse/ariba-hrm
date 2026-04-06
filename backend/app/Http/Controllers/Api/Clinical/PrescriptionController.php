<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePrescriptionPdfJob;
use App\Models\Consultation;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    public function store(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.drug_id' => ['nullable', 'integer', 'exists:drugs,id'],
            'items.*.medicine_name' => ['required_without:items.*.drug_id', 'nullable', 'string', 'max:255'],
            'items.*.dosage' => ['nullable', 'string', 'max:100'],
            'items.*.frequency' => ['nullable', 'string', 'max:100'],
            'items.*.duration' => ['nullable', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $patient = Patient::query()->with('history')->findOrFail($consultation->patient_id);
        $allergyAlerts = [];

        $prescription = DB::transaction(function () use ($request, $consultation, $data, $patient, &$allergyAlerts): Prescription {
            $prescription = Prescription::create([
                'tenant_id' => $request->attributes->get('tenant_id'),
                'consultation_id' => $consultation->id,
                'prescribed_by' => $request->user()?->id,
                'instructions' => $data['instructions'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $drug = ! empty($item['drug_id']) ? Drug::query()->find($item['drug_id']) : null;
                $medicineName = $item['medicine_name'] ?? ($drug?->brand_name ?: $drug?->generic_name ?: 'Unknown Drug');

                $matchedAllergies = $this->matchAllergies(
                    (string) ($patient->history?->allergies ?? ''),
                    $medicineName
                );

                if ($matchedAllergies !== []) {
                    $allergyAlerts[] = [
                        'medicine_name' => $medicineName,
                        'matched_allergies' => $matchedAllergies,
                    ];
                }

                $prescription->items()->create([
                    'drug_id' => $item['drug_id'] ?? null,
                    'medicine_name' => $medicineName,
                    'dosage' => $item['dosage'] ?? null,
                    'frequency' => $item['frequency'] ?? null,
                    'duration' => $item['duration'] ?? null,
                    'route' => $item['route'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $printable = "Prescription #{$prescription->id}\n";
            foreach ($prescription->items as $prescriptionItem) {
                $printable .= "- {$prescriptionItem->medicine_name} | {$prescriptionItem->dosage} | {$prescriptionItem->frequency} | {$prescriptionItem->duration}\n";
            }
            $printable .= 'Instructions: '.($prescription->instructions ?? 'N/A');

            $prescription->update(['printable_content' => $printable]);

            return $prescription->fresh('items');
        });

        GeneratePrescriptionPdfJob::dispatch($prescription->id);

        return response()->json([
            'message' => 'Prescription created and PDF generation queued.',
            'prescription' => $prescription,
            'allergy_alerts' => $allergyAlerts,
        ], 201);
    }

    public function pdfUrl(Request $request, Prescription $prescription): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        if ($prescription->tenant_id !== null && (int) $prescription->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Prescription not found.'], 404);
        }

        if (! $prescription->pdf_path) {
            return response()->json(['message' => 'Prescription PDF is not ready yet.'], 422);
        }

        $disk = config('filesystems.default', 'local');
        $storage = Storage::disk($disk);

        if (! $storage->exists($prescription->pdf_path)) {
            return response()->json(['message' => 'Prescription PDF not found.'], 404);
        }

        $url = method_exists($storage, 'temporaryUrl')
            ? $storage->temporaryUrl($prescription->pdf_path, now()->addMinutes(15))
            : $storage->url($prescription->pdf_path);

        return response()->json([
            'prescription_id' => $prescription->id,
            'pdf_url' => $url,
            'expires_in_minutes' => 15,
        ]);
    }

    private function matchAllergies(string $allergyText, string $medicineName): array
    {
        if (trim($allergyText) === '' || trim($medicineName) === '') {
            return [];
        }

        $medicine = Str::lower($medicineName);

        $terms = collect(preg_split('/[,;\n]+/', $allergyText) ?: [])
            ->map(fn ($v) => trim(Str::lower((string) $v)))
            ->filter(fn ($v) => mb_strlen($v) >= 3)
            ->values();

        return $terms
            ->filter(fn (string $term) => str_contains($medicine, $term) || str_contains($term, $medicine))
            ->values()
            ->all();
    }
}
