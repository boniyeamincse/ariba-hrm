<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReferralLetterPdfJob;
use App\Models\Consultation;
use App\Models\Referral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReferralController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'consultation_id' => ['required', 'integer', 'exists:consultations,id'],
            'referral_type' => ['required', 'in:internal,external'],
            'target_department' => ['nullable', 'string', 'max:120'],
            'target_specialist' => ['nullable', 'string', 'max:120'],
            'external_facility' => ['nullable', 'string', 'max:180'],
            'reason' => ['nullable', 'string'],
            'clinical_notes' => ['nullable', 'string'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $consultation = Consultation::query()->findOrFail($data['consultation_id']);

        $referral = Referral::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'referred_by' => $request->user()?->id,
            'referral_type' => $data['referral_type'],
            'target_department' => $data['target_department'] ?? null,
            'target_specialist' => $data['target_specialist'] ?? null,
            'external_facility' => $data['external_facility'] ?? null,
            'reason' => $data['reason'] ?? null,
            'clinical_notes' => $data['clinical_notes'] ?? null,
            'status' => $data['referral_type'] === 'internal' ? 'accepted' : 'initiated',
            'follow_up_at' => $data['follow_up_at'] ?? null,
        ]);

        if ($referral->referral_type === 'external') {
            GenerateReferralLetterPdfJob::dispatch($referral->id);
        }

        return response()->json([
            'message' => 'Referral created successfully.',
            'referral' => $referral,
        ], 201);
    }

    public function generateLetter(Referral $referral): JsonResponse
    {
        GenerateReferralLetterPdfJob::dispatch($referral->id);

        return response()->json([
            'message' => 'Referral letter generation queued.',
            'referral_id' => $referral->id,
        ]);
    }

    public function show(Referral $referral): JsonResponse
    {
        $downloadUrl = null;
        if ($referral->letter_pdf_path) {
            $disk = config('filesystems.default', 'local');
            $storage = Storage::disk($disk);
            $downloadUrl = method_exists($storage, 'temporaryUrl')
                ? $storage->temporaryUrl($referral->letter_pdf_path, now()->addMinutes(15))
                : $storage->url($referral->letter_pdf_path);
        }

        return response()->json([
            'referral' => $referral,
            'letter_url' => $downloadUrl,
        ]);
    }
}
