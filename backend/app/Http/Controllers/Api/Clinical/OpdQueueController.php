<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Events\OpdQueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\OpdQueue;
use App\Models\PatientVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdQueueController extends Controller
{
    public function generateToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'priority' => ['nullable', 'integer', 'between:0,9'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');

        $queue = DB::transaction(function () use ($tenantId, $data): OpdQueue {
            $tokenNo = (int) OpdQueue::query()
                ->where('tenant_id', $tenantId)
                ->max('token_no') + 1;

            $visit = PatientVisit::create([
                'tenant_id' => $tenantId,
                'patient_id' => $data['patient_id'],
                'visit_type' => 'opd',
                'reference_no' => 'OPD-'.now()->format('Ymd').'-'.$tokenNo,
                'visit_at' => now(),
                'status' => 'active',
            ]);

            return OpdQueue::create([
                'tenant_id' => $tenantId,
                'patient_id' => $data['patient_id'],
                'patient_visit_id' => $visit->id,
                'token_no' => $tokenNo,
                'priority' => $data['priority'] ?? 0,
                'status' => 'waiting',
                'queued_at' => now(),
            ]);
        });

        event(new OpdQueueUpdated($tenantId, 'token_generated', [
            'queue_id' => $queue->id,
            'token_no' => $queue->token_no,
            'status' => $queue->status,
        ]));

        return response()->json([
            'message' => 'OPD token generated.',
            'queue' => $queue,
        ], 201);
    }

    public function state(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $current = OpdQueue::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'with_doctor')
            ->orderBy('updated_at')
            ->first();

        $waiting = OpdQueue::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'waiting')
            ->orderByDesc('priority')
            ->orderBy('token_no')
            ->get();

        return response()->json([
            'current' => $current,
            'waiting' => $waiting,
            'stats' => [
                'waiting_count' => $waiting->count(),
                'served_today' => OpdQueue::query()
                    ->where('tenant_id', $tenantId)
                    ->whereDate('updated_at', today())
                    ->where('status', 'completed')
                    ->count(),
            ],
        ]);
    }

    public function callNext(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $next = DB::transaction(function () use ($tenantId): ?OpdQueue {
            OpdQueue::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'with_doctor')
                ->update(['status' => 'completed']);

            $next = OpdQueue::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'waiting')
                ->orderByDesc('priority')
                ->orderBy('token_no')
                ->lockForUpdate()
                ->first();

            if (! $next) {
                return null;
            }

            $next->update(['status' => 'with_doctor']);

            return $next;
        });

        if (! $next) {
            return response()->json(['message' => 'No waiting patients in queue.'], 404);
        }

        event(new OpdQueueUpdated($tenantId, 'called_next', [
            'queue_id' => $next->id,
            'token_no' => $next->token_no,
            'status' => $next->status,
        ]));

        return response()->json([
            'message' => 'Next patient called.',
            'queue' => $next,
        ]);
    }

    public function skip(Request $request, OpdQueue $opdQueue): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        if ((int) $opdQueue->tenant_id !== (int) $tenantId) {
            return response()->json(['message' => 'Queue item not found.'], 404);
        }

        if ($opdQueue->status !== 'waiting') {
            return response()->json(['message' => 'Only waiting token can be skipped.'], 422);
        }

        $opdQueue->update(['status' => 'skipped']);

        event(new OpdQueueUpdated($tenantId, 'skipped', [
            'queue_id' => $opdQueue->id,
            'token_no' => $opdQueue->token_no,
            'status' => $opdQueue->status,
        ]));

        return response()->json([
            'message' => 'Queue token skipped.',
            'queue' => $opdQueue,
        ]);
    }
}
