<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSickLeaveCertificatePdfJob;
use App\Models\Consultation;
use App\Models\OpdQueue;
use App\Models\SickLeaveCertificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Meilisearch\Client as MeilisearchClient;
use Throwable;

class ConsultationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'opd_queue_id' => ['nullable', 'integer', 'exists:opd_queues,id'],
            'subjective' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'icd10_code' => ['nullable', 'string', 'max:30'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $consultation = Consultation::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'doctor_id' => $request->user()?->id,
            'opd_queue_id' => $data['opd_queue_id'] ?? null,
            'subjective' => $data['subjective'] ?? null,
            'objective' => $data['objective'] ?? null,
            'complaint' => $data['subjective'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'diagnosis_code' => $data['icd10_code'] ?? null,
            'icd10_code' => $data['icd10_code'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
        ]);

        if (! empty($data['opd_queue_id'])) {
            OpdQueue::query()->whereKey($data['opd_queue_id'])->update(['status' => 'completed']);
        }

        return response()->json([
            'message' => 'SOAP consultation saved.',
            'consultation' => $consultation,
        ], 201);
    }

    public function searchIcd10(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        try {
            $host = (string) config('scout.meilisearch.host', env('MEILISEARCH_HOST', 'http://meilisearch:7700'));
            $key = (string) config('scout.meilisearch.key', env('MEILISEARCH_KEY'));
            $client = new MeilisearchClient($host, $key ?: null);
            $result = $client->index('icd10_codes')->search($query, ['limit' => 20]);

            return response()->json([
                'data' => $result->getHits(),
                'source' => 'meilisearch',
            ]);
        } catch (Throwable) {
            $fallback = collect($this->fallbackIcd10())
                ->filter(function (array $row) use ($query): bool {
                    $q = mb_strtolower($query);

                    return str_contains(mb_strtolower($row['code']), $q)
                        || str_contains(mb_strtolower($row['label']), $q);
                })
                ->take(20)
                ->values();

            return response()->json([
                'data' => $fallback,
                'source' => 'fallback',
            ]);
        }
    }

    public function createSickLeaveCertificate(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'leave_from' => ['required', 'date'],
            'leave_to' => ['required', 'date', 'after_or_equal:leave_from'],
            'reason' => ['nullable', 'string'],
            'doctor_signature_name' => ['nullable', 'string', 'max:255'],
        ]);

        $start = Carbon::parse($data['leave_from']);
        $end = Carbon::parse($data['leave_to']);
        $daysCount = (int) $start->diffInDays($end) + 1;

        $certificate = SickLeaveCertificate::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $request->user()?->id,
            'leave_from' => $start->toDateString(),
            'leave_to' => $end->toDateString(),
            'days_count' => $daysCount,
            'reason' => $data['reason'] ?? null,
            'doctor_signature_name' => $data['doctor_signature_name'] ?? $request->user()?->name,
        ]);

        GenerateSickLeaveCertificatePdfJob::dispatch($certificate->id);

        return response()->json([
            'message' => 'Sick leave certificate created and PDF generation queued.',
            'certificate' => $certificate,
        ], 201);
    }

    public function sickLeaveCertificateUrl(Request $request, SickLeaveCertificate $certificate): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        if ($certificate->tenant_id !== null && (int) $certificate->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Certificate not found.'], 404);
        }

        if (! $certificate->pdf_path) {
            return response()->json(['message' => 'Certificate PDF is not ready yet.'], 422);
        }

        $disk = config('filesystems.default', 'local');
        $storage = Storage::disk($disk);

        if (! $storage->exists($certificate->pdf_path)) {
            return response()->json(['message' => 'Certificate PDF not found.'], 404);
        }

        $url = method_exists($storage, 'temporaryUrl')
            ? $storage->temporaryUrl($certificate->pdf_path, now()->addMinutes(15))
            : $storage->url($certificate->pdf_path);

        return response()->json([
            'certificate_id' => $certificate->id,
            'pdf_url' => $url,
            'expires_in_minutes' => 15,
        ]);
    }

    private function fallbackIcd10(): array
    {
        return [
            ['code' => 'J06.9', 'label' => 'Acute upper respiratory infection, unspecified'],
            ['code' => 'I10', 'label' => 'Essential (primary) hypertension'],
            ['code' => 'E11.9', 'label' => 'Type 2 diabetes mellitus without complications'],
            ['code' => 'R50.9', 'label' => 'Fever, unspecified'],
            ['code' => 'R07.9', 'label' => 'Chest pain, unspecified'],
            ['code' => 'K29.7', 'label' => 'Gastritis, unspecified'],
            ['code' => 'M54.5', 'label' => 'Low back pain'],
            ['code' => 'N39.0', 'label' => 'Urinary tract infection, site not specified'],
            ['code' => 'A09', 'label' => 'Infectious gastroenteritis and colitis, unspecified'],
            ['code' => 'J45.9', 'label' => 'Asthma, unspecified'],
        ];
    }
}
