<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchItem;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Warehouse;
use App\Services\WarehouseTransferService;
use Illuminate\Http\Request;

class WarehouseTransferController extends Controller
{
    public function index(Request $request, WarehouseTransferService $service)
    {
        $filters = [
            'from_warehouse_id' => $request->integer('from_warehouse_id') ?: null,
            'to_warehouse_id' => $request->integer('to_warehouse_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
        ];

        return view('dashboard.inventory.transfers', [
            'movements' => $service->movements($filters),
            'filters' => $filters,
            'warehouses' => Warehouse::orderBy('name_ar')->get(),
            'items' => Item::orderBy('name_ar')->get(),
            'batchItems' => BatchItem::with(['batch', 'item', 'pack', 'stock'])
                ->orderByDesc('id')
                ->limit(200)
                ->get(),
            'packs' => ItemPack::with(['item', 'packType'])->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, WarehouseTransferService $service)
    {
        $validated = $request->validate([
            'movement_type' => ['required', 'in:transfer,out'],
            'batch_item_id' => ['required_without:item_pack_id', 'nullable', 'exists:batch_items,id'],
            'item_pack_id' => ['required_if:movement_type,out', 'nullable', 'exists:item_packs,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $from = Warehouse::findOrFail($validated['from_warehouse_id']);

        try {
            if ($validated['movement_type'] === 'transfer') {
                $service->transfer(
                    BatchItem::findOrFail($validated['batch_item_id']),
                    $from,
                    Warehouse::findOrFail($validated['to_warehouse_id']),
                    (float) $validated['quantity'],
                    $request->user(),
                    $validated['notes'] ?? null,
                );
            } else {
                $service->dispenseFifo(
                    ItemPack::findOrFail($validated['item_pack_id']),
                    $from,
                    (float) $validated['quantity'],
                    $request->user(),
                    $validated['notes'] ?? null,
                );
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', trans('messages.inventory_page.transfer_done'));
    }
}
