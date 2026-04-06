<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientHistory;
use App\Services\UhidService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $search = trim((string) $request->query('q', ''));

        if ($search !== '' && config('scout.driver') === 'meilisearch') {
            $scout = Patient::search($search);

            if ($tenantId) {
                $scout->where('tenant_id', (int) $tenantId);
            }

            return response()->json([
                'data' => $scout->take(50)->get(),
                'search_engine' => 'meilisearch',
            ]);
        }

        $patients = Patient::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('uhid', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('national_id_no', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($patients);
    }

    public function store(Request $request, UhidService $uhidService): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        $data = $request->validate($this->rules());

        $duplicateMatch = $this->findDuplicatePatient($tenantId, $data);

        $data['tenant_id'] = $tenantId;
        $data['uhid'] = $uhidService->generate($tenantId);

        $patient = Patient::create($data);

        PatientHistory::create([
            'patient_id' => $patient->id,
        ]);

        $this->syncSearchIndex($patient);

        return response()->json([
            'message' => 'Patient registered successfully.',
            'duplicate_detected' => $duplicateMatch !== null,
            'duplicate_match' => $duplicateMatch,
            'patient' => $patient->fresh('history'),
        ], 201);
    }

    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        return response()->json([
            'patient' => $patient->load(['history', 'visits']),
        ]);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        $data = $request->validate($this->rules(false));

        $patient->fill($data);
        $patient->save();

        $this->syncSearchIndex($patient);

        return response()->json([
            'message' => 'Patient updated successfully.',
            'patient' => $patient->fresh('history'),
        ]);
    }

    public function uploadPhoto(Request $request, Patient $patient): JsonResponse
    {
        $this->ensureTenantAccess($request, $patient);

        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        /** @var UploadedFile $photo */
        $photo = $data['photo'];
        $tenantId = $this->resolveTenantId($request) ?: 'global';

        $extension = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
        $basePath = 'tenants/'.$tenantId.'/patients/'.$patient->id;
        $photoPath = $basePath.'/photo.'.$extension;
        $thumbPath = $basePath.'/photo_thumb.jpg';

        Storage::disk('s3')->putFileAs($basePath, $photo, 'photo.'.$extension);

        $thumbContent = $this->makeThumbnail($photo->get());
        Storage::disk('s3')->put($thumbPath, $thumbContent, [
            'visibility' => 'public',
            'ContentType' => 'image/jpeg',
        ]);

        $patient->update([
            'photo_path' => $photoPath,
            'photo_thumb_path' => $thumbPath,
        ]);

        return response()->json([
            'message' => 'Patient photo uploaded successfully.',
            'patient' => $patient->fresh(),
        ]);
    }

    private function rules(bool $isCreate = true): array
    {
        $firstNameRule = $isCreate ? ['required', 'string', 'max:120'] : ['sometimes', 'string', 'max:120'];

        return [
            'first_name' => $firstNameRule,
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id_no' => ['nullable', 'string', 'max:40'],
            'passport_no' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function ensureTenantAccess(Request $request, Patient $patient): void
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId && (int) $patient->tenant_id !== (int) $tenantId) {
            abort(404);
        }
    }

    private function resolveTenantId(Request $request): ?int
    {
        $tenantId = $request->attributes->get('tenant_id');

        if ($tenantId !== null) {
            return (int) $tenantId;
        }

        return $request->user()?->tenant_id ? (int) $request->user()->tenant_id : null;
    }

    private function findDuplicatePatient(?int $tenantId, array $data): ?array
    {
        if (empty($data['first_name']) || empty($data['date_of_birth']) || empty($data['phone'])) {
            return null;
        }

        $duplicate = Patient::query()
            ->when($tenantId, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->where('first_name', $data['first_name'])
            ->whereDate('date_of_birth', $data['date_of_birth'])
            ->where('phone', $data['phone'])
            ->first();

        if (! $duplicate) {
            return null;
        }

        return [
            'id' => $duplicate->id,
            'uhid' => $duplicate->uhid,
            'name' => trim($duplicate->first_name.' '.($duplicate->last_name ?? '')),
            'phone' => $duplicate->phone,
            'date_of_birth' => optional($duplicate->date_of_birth)->toDateString(),
        ];
    }

    private function syncSearchIndex(Patient $patient): void
    {
        try {
            $patient->searchable();
        } catch (Throwable) {
            // Meilisearch may be unavailable in local/test environments.
        }
    }

    private function makeThumbnail(string $binary): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $binary;
        }

        $source = @imagecreatefromstring($binary);

        if (! $source) {
            return $binary;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);

            return $binary;
        }

        $max = 256;
        $ratio = min($max / $sourceWidth, $max / $sourceHeight, 1);
        $targetWidth = max((int) floor($sourceWidth * $ratio), 1);
        $targetHeight = max((int) floor($sourceHeight * $ratio), 1);

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        ob_start();
        imagejpeg($target, null, 85);
        $jpeg = (string) ob_get_clean();

        imagedestroy($target);
        imagedestroy($source);

        return $jpeg;
    }
}
