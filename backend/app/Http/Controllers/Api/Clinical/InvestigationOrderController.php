<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\InvestigationOrder;
use App\Models\LabOrder;
use App\Models\LabTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestigationOrderController extends Controller
{
    public function store(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'orders' => ['required', 'array', 'min:1'],
            'orders.*.order_type' => ['required', 'in:lab,radiology'],
            'orders.*.lab_test_id' => ['nullable', 'integer', 'exists:lab_tests,id'],
            'orders.*.test_name' => ['required', 'string', 'max:255'],
            'orders.*.notes' => ['nullable', 'string'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $created = [];

        foreach ($data['orders'] as $orderData) {
            $order = InvestigationOrder::create([
                'tenant_id' => $tenantId,
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'order_type' => $orderData['order_type'],
                'test_name' => $orderData['test_name'],
                'notes' => $orderData['notes'] ?? null,
                'status' => 'ordered',
            ]);

            if ($orderData['order_type'] === 'lab') {
                $labTestId = $orderData['lab_test_id'] ?? null;

                if (! $labTestId) {
                    $matchedTest = LabTest::query()
                        ->where('tenant_id', $tenantId)
                        ->where('name', $orderData['test_name'])
                        ->first();

                    $labTestId = $matchedTest?->id;
                }

                $labOrder = LabOrder::create([
                    'tenant_id' => $tenantId,
                    'patient_id' => $consultation->patient_id,
                    'consultation_id' => $consultation->id,
                    'lab_test_id' => $labTestId,
                    'notes' => $orderData['notes'] ?? null,
                    'status' => 'ordered',
                    'ordered_at' => now(),
                ]);

                $order->update([
                    'routed_module' => 'lab',
                    'routed_reference' => (string) $labOrder->id,
                ]);
            } else {
                $order->update([
                    'routed_module' => 'radiology',
                    'routed_reference' => 'RAD-'.now()->format('YmdHis').'-'.$order->id,
                ]);
            }

            $created[] = $order->fresh();
        }

        return response()->json([
            'message' => 'Investigation orders created and routed.',
            'orders' => $created,
        ], 201);
    }
}
