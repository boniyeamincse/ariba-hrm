<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceCharge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function charges(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $data = ServiceCharge::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }

    public function storeCharge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'service_name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', 'max:60'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['tenant_id'] = $request->attributes->get('tenant_id');

        $charge = ServiceCharge::create($data);

        return response()->json(['charge' => $charge], 201);
    }

    public function createInvoice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'patient_visit_id' => ['nullable', 'integer', 'exists:patient_visits,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.charge_code' => ['nullable', 'string', 'max:60'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $discount = (float) ($data['discount'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);

        $invoice = DB::transaction(function () use ($data, $tenantId, $discount, $tax): Invoice {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * (float) $item['unit_price'];
            }

            $totalDue = max(0, $subtotal - $discount + $tax);

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'patient_id' => $data['patient_id'],
                'patient_visit_id' => $data['patient_visit_id'] ?? null,
                'invoice_no' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_due' => $totalDue,
                'amount_paid' => 0,
                'status' => 'unpaid',
                'issued_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity'] * (float) $item['unit_price'];

                $invoice->items()->create([
                    'charge_code' => $item['charge_code'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            return $invoice->fresh('items');
        });

        return response()->json(['invoice' => $invoice], 201);
    }

    public function addPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:40'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_ref' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'received_by' => $request->user()?->id,
            'paid_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $invoice->amount_paid = (float) $invoice->amount_paid + (float) $payment->amount;
        $invoice->status = $invoice->amount_paid >= (float) $invoice->total_due ? 'paid' : 'partial';
        $invoice->save();

        return response()->json([
            'message' => 'Payment captured.',
            'invoice' => $invoice,
            'payment' => $payment,
        ]);
    }

    public function approveDiscount(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'discount' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice->discount = $data['discount'];
        $invoice->approved_discount_by = $request->user()?->id;
        $invoice->total_due = max(0, (float) $invoice->subtotal - (float) $invoice->discount + (float) $invoice->tax);
        $invoice->status = (float) $invoice->amount_paid >= (float) $invoice->total_due ? 'paid' : 'partial';
        if ((float) $invoice->amount_paid === 0) {
            $invoice->status = 'unpaid';
        }
        $invoice->save();

        return response()->json([
            'message' => 'Discount approved.',
            'invoice' => $invoice,
        ]);
    }
}
