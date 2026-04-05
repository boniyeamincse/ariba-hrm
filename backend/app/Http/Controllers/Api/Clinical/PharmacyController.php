<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\PharmacySale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    public function drugs(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = Drug::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }

    public function storeDrug(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'generic_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'dosage_form' => ['nullable', 'string', 'max:50'],
            'strength' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['tenant_id'] = $request->attributes->get('tenant_id');
        $data['unit_price'] = $data['unit_price'] ?? 0;

        $drug = Drug::create($data);

        return response()->json(['drug' => $drug], 201);
    }

    public function addBatch(Request $request, Drug $drug): JsonResponse
    {
        $data = $request->validate([
            'batch_no' => ['required', 'string', 'max:80'],
            'expiry_date' => ['nullable', 'date'],
            'quantity_received' => ['required', 'integer', 'min:1'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $batch = DrugBatch::create([
            'drug_id' => $drug->id,
            'batch_no' => $data['batch_no'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'quantity_received' => $data['quantity_received'],
            'quantity_available' => $data['quantity_received'],
            'purchase_price' => $data['purchase_price'] ?? 0,
            'selling_price' => $data['selling_price'] ?? $drug->unit_price,
        ]);

        return response()->json(['batch' => $batch], 201);
    }

    public function dispense(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'prescription_id' => ['nullable', 'integer', 'exists:prescriptions,id'],
            'sale_type' => ['required', 'in:prescription,counter'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.drug_batch_id' => ['required', 'integer', 'exists:drug_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $discount = (float) ($data['discount'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);

        $sale = DB::transaction(function () use ($request, $data, $tenantId, $discount, $tax): PharmacySale {
            $subtotal = 0;
            $itemsPayload = [];

            foreach ($data['items'] as $item) {
                $batch = DrugBatch::query()->lockForUpdate()->findOrFail($item['drug_batch_id']);

                if ($batch->quantity_available < $item['quantity']) {
                    abort(422, 'Insufficient stock for batch '.$batch->batch_no);
                }

                $lineTotal = $item['quantity'] * (float) $batch->selling_price;
                $subtotal += $lineTotal;

                $batch->decrement('quantity_available', $item['quantity']);

                $itemsPayload[] = [
                    'drug_batch_id' => $batch->id,
                    'drug_name' => optional($batch->drug)->generic_name ?? 'Unknown Drug',
                    'quantity' => $item['quantity'],
                    'unit_price' => $batch->selling_price,
                    'line_total' => $lineTotal,
                ];
            }

            $total = max(0, $subtotal - $discount + $tax);

            $sale = PharmacySale::create([
                'tenant_id' => $tenantId,
                'patient_id' => $data['patient_id'] ?? null,
                'prescription_id' => $data['prescription_id'] ?? null,
                'sale_type' => $data['sale_type'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'completed',
                'dispensed_by' => $request->user()?->id,
                'sold_at' => now(),
            ]);

            foreach ($itemsPayload as $payload) {
                $sale->items()->create($payload);
            }

            return $sale->fresh('items');
        });

        return response()->json([
            'message' => 'Dispensing completed.',
            'sale' => $sale,
        ], 201);
    }
}
