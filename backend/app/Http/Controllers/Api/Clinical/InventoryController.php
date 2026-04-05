<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ProcurementOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function items(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $data = InventoryItem::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }

    public function createItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:80'],
            'stock_on_hand' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = InventoryItem::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'sku' => $data['sku'],
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'stock_on_hand' => $data['stock_on_hand'] ?? 0,
            'reorder_level' => $data['reorder_level'] ?? 0,
            'unit_cost' => $data['unit_cost'] ?? 0,
        ]);

        return response()->json(['item' => $item], 201);
    }

    public function createProcurementOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['quantity'] * (float) $item['unit_price'];
        }

        $po = ProcurementOrder::create([
            'tenant_id' => $request->attributes->get('tenant_id'),
            'po_no' => 'PO-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'supplier_name' => $data['supplier_name'],
            'status' => 'ordered',
            'total_amount' => $total,
            'ordered_at' => now(),
        ]);

        foreach ($data['items'] as $item) {
            $lineTotal = $item['quantity'] * (float) $item['unit_price'];
            $po->items()->create([
                'inventory_item_id' => $item['inventory_item_id'] ?? null,
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);

            if (! empty($item['inventory_item_id'])) {
                InventoryItem::query()->whereKey($item['inventory_item_id'])->increment('stock_on_hand', $item['quantity']);
            }
        }

        return response()->json(['procurement_order' => $po->fresh('items')], 201);
    }
}
