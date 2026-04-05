<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\InvestigationOrder;
use App\Models\OpdQueue;
use App\Models\PatientVisit;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdController extends Controller
{
    public function enqueue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'priority' => ['nullable', 'integer', 'between:0,9'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');

        $tokenNo = (int) OpdQueue::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->max('token_no') + 1;

        $visit = PatientVisit::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'visit_type' => 'opd',
            'reference_no' => 'OPD-'.now()->format('Ymd').'-'.$tokenNo,
            'visit_at' => now(),
            'status' => 'active',
        ]);

        $queue = OpdQueue::create([
            'tenant_id' => $tenantId,
            'patient_id' => $data['patient_id'],
            'patient_visit_id' => $visit->id,
            'token_no' => $tokenNo,
            'priority' => $data['priority'] ?? 0,
            'status' => 'waiting',
            'queued_at' => now(),
        ]);

        return response()->json([
            'message' => 'Patient added to OPD queue.',
            'queue' => $queue,
        ], 201);
    }

    public function queueList(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $items = OpdQueue::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereIn('status', ['waiting', 'with_doctor'])
            ->orderByDesc('priority')
            ->orderBy('token_no')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function consult(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'opd_queue_id' => ['nullable', 'integer', 'exists:opd_queues,id'],
            'complaint' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'diagnosis_code' => ['nullable', 'string', 'max:30'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $consultation = Consultation::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'doctor_id' => $request->user()?->id,
            'opd_queue_id' => $data['opd_queue_id'] ?? null,
            'complaint' => $data['complaint'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'diagnosis_code' => $data['diagnosis_code'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
        ]);

        if (! empty($data['opd_queue_id'])) {
            OpdQueue::query()->whereKey($data['opd_queue_id'])->update(['status' => 'completed']);
        }

        return response()->json([
            'message' => 'Consultation recorded.',
            'consultation' => $consultation,
        ], 201);
    }

    public function prescribe(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['nullable', 'string', 'max:100'],
            'items.*.frequency' => ['nullable', 'string', 'max:100'],
            'items.*.duration' => ['nullable', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $prescription = DB::transaction(function () use ($request, $consultation, $data): Prescription {
            $prescription = Prescription::create([
                'tenant_id' => $request->attributes->get('tenant_id'),
                'consultation_id' => $consultation->id,
                'prescribed_by' => $request->user()?->id,
                'instructions' => $data['instructions'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $prescription->items()->create($item);
            }

            $printable = "Prescription #{$prescription->id}\n";
            foreach ($prescription->items as $item) {
                $printable .= "- {$item->medicine_name} | {$item->dosage} | {$item->frequency} | {$item->duration}\n";
            }
            $printable .= "Instructions: ".($prescription->instructions ?? 'N/A');

            $prescription->update(['printable_content' => $printable]);

            return $prescription->fresh('items');
        });

        return response()->json([
            'message' => 'Prescription created.',
            'prescription' => $prescription,
            'printable_content' => $prescription->printable_content,
        ], 201);
    }

    public function orderInvestigations(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'orders' => ['required', 'array', 'min:1'],
            'orders.*.order_type' => ['required', 'in:lab,radiology'],
            'orders.*.test_name' => ['required', 'string', 'max:255'],
            'orders.*.notes' => ['nullable', 'string'],
        ]);

        $created = [];
        foreach ($data['orders'] as $order) {
            $created[] = InvestigationOrder::create([
                'tenant_id' => $request->attributes->get('tenant_id'),
                'consultation_id' => $consultation->id,
                'order_type' => $order['order_type'],
                'test_name' => $order['test_name'],
                'notes' => $order['notes'] ?? null,
                'status' => 'ordered',
            ]);
        }

        return response()->json([
            'message' => 'Investigation orders created.',
            'orders' => $created,
        ], 201);
    }
}
