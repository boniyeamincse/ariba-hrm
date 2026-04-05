<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\DischargeClearance;
use App\Models\Invoice;
use App\Models\IpdAdmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DischargeController extends Controller
{
    public function clear(Request $request, IpdAdmission $admission): JsonResponse
    {
        $data = $request->validate([
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'pharmacy_cleared' => ['required', 'boolean'],
            'lab_cleared' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = null;
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
        }

        $billingCleared = $invoice ? $invoice->status === 'paid' : false;
        $status = ($data['pharmacy_cleared'] && $data['lab_cleared'] && $billingCleared) ? 'cleared' : 'pending';

        $clearance = DischargeClearance::updateOrCreate(
            ['ipd_admission_id' => $admission->id],
            [
                'tenant_id' => $request->attributes->get('tenant_id'),
                'invoice_id' => $data['invoice_id'] ?? null,
                'pharmacy_cleared' => $data['pharmacy_cleared'],
                'lab_cleared' => $data['lab_cleared'],
                'billing_cleared' => $billingCleared,
                'cleared_by' => $request->user()?->id,
                'cleared_at' => $status === 'cleared' ? now() : null,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
            ]
        );

        if ($status === 'cleared') {
            $admission->update([
                'status' => 'discharge_cleared',
                'discharged_at' => now(),
            ]);

            if ($admission->bed_id) {
                $admission->bed()->update(['is_occupied' => false]);
            }
        }

        return response()->json([
            'message' => 'Discharge clearance processed.',
            'clearance' => $clearance,
            'billing_cleared' => $billingCleared,
        ]);
    }
}
