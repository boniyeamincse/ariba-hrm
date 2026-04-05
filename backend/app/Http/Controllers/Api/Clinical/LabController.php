<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\LabSample;
use App\Models\LabTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function tests(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = LabTest::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }

    public function storeTest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:60'],
            'sample_type' => ['nullable', 'string', 'max:60'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['tenant_id'] = $request->attributes->get('tenant_id');
        $data['price'] = $data['price'] ?? 0;

        $test = LabTest::create($data);

        return response()->json(['test' => $test], 201);
    }

    public function collectSample(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'patient_visit_id' => ['nullable', 'integer', 'exists:patient_visits,id'],
        ]);

        $sample = LabSample::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'patient_visit_id' => $data['patient_visit_id'] ?? null,
            'barcode' => 'LAB-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'status' => 'collected',
            'collected_by' => $request->user()?->id,
            'collected_at' => now(),
        ]);

        return response()->json(['sample' => $sample], 201);
    }

    public function order(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'consultation_id' => ['nullable', 'integer', 'exists:consultations,id'],
            'sample_id' => ['nullable', 'integer', 'exists:lab_samples,id'],
            'lab_test_id' => ['required', 'integer', 'exists:lab_tests,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = LabOrder::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'patient_id' => $data['patient_id'],
            'consultation_id' => $data['consultation_id'] ?? null,
            'sample_id' => $data['sample_id'] ?? null,
            'lab_test_id' => $data['lab_test_id'],
            'notes' => $data['notes'] ?? null,
            'status' => 'ordered',
            'ordered_at' => now(),
        ]);

        return response()->json(['order' => $order], 201);
    }

    public function enterResult(Request $request, LabOrder $order): JsonResponse
    {
        $data = $request->validate([
            'result_value' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:40'],
            'reference_range' => ['nullable', 'string', 'max:255'],
            'is_abnormal' => ['nullable', 'boolean'],
        ]);

        $result = LabResult::updateOrCreate(
            ['lab_order_id' => $order->id],
            [
                'tenant_id' => $request->attributes->get('tenant_id'),
                'result_value' => $data['result_value'],
                'unit' => $data['unit'] ?? null,
                'reference_range' => $data['reference_range'] ?? null,
                'is_abnormal' => $data['is_abnormal'] ?? false,
            ]
        );

        $order->update(['status' => 'result_entered']);

        return response()->json(['result' => $result]);
    }

    public function validateResult(Request $request, LabResult $result): JsonResponse
    {
        $report = "Lab Report #{$result->id}\n";
        $report .= "Result: {$result->result_value} {$result->unit}\n";
        $report .= "Reference: {$result->reference_range}\n";
        $report .= $result->is_abnormal ? 'Flag: ABNORMAL' : 'Flag: Normal';

        $result->update([
            'validated_by' => $request->user()?->id,
            'validated_at' => now(),
            'report_content' => $report,
        ]);

        $result->labOrder()->update(['status' => 'validated']);

        return response()->json([
            'message' => 'Lab result validated.',
            'result' => $result,
        ]);
    }

    public function report(LabResult $result): JsonResponse
    {
        return response()->json([
            'result_id' => $result->id,
            'report_content' => $result->report_content,
            'validated_at' => $result->validated_at,
        ]);
    }
}
