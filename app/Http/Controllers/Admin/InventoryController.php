<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
         return view('dashboard.inventory.index', [
            'inventories' => \App\Models\Inventory::with(['item', 'pack', 'warehouse'])
                ->latest()
                ->paginate(20),
            'lowStockCount' => 0,
            'warehouses' => collect(),
            'filters' => [],
        ]);
    }

    public function movements()
    {
        return view('dashboard.inventory.movements', [
            'movements' => \App\Models\InventoryMovement::orderBy('created_at', 'desc')->paginate(15),
            'filters' => [],
            'items' => \App\Models\Item::all(),
            'warehouses' => \App\Models\Warehouse::all(),
        ]);
    }

    public function adjust(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'new_quantity' => 'required|numeric',
            'reason' => 'required|string',
        ]);

        $service = app(\App\Contracts\InventoryServiceInterface::class);
        $service->adjust(
            (int) $validated['inventory_id'],
            (float) $validated['new_quantity'],
            $validated['reason'],
            auth()->id(),
        );

        return redirect()->route('inventory.index')->with('success', 'تم تعديل المخزون');
    }
}
