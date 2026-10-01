<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\LowStockAlertService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private LowStockAlertService $lowStock)
    {
    }

    /**
     * Stock by warehouse, then by item, pack and batch, with the per-batch
     * minimum and whether that position is low or expired.
     */
    public function index(Request $request)
    {
        $filters = [
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
            'only_low' => $request->boolean('only_low') ?: null,
        ];

        $rows = $this->lowStock->stockRows($filters);

        $byWarehouse = $rows
            ->groupBy('warehouse_id')
            ->map(fn ($items, $warehouseId) => [
                'warehouse' => Warehouse::find($warehouseId),
                'rows' => $items->sortBy('name')->values(),
                'low_count' => $items->where('is_low', true)->count(),
                'expired_count' => $items->where('is_expired', true)->count(),
                'total_available' => round($items->sum('available_quantity'), 2),
            ])
            ->sortBy(fn ($group) => $group['warehouse']?->name_ar ?: '')
            ->values();

        return view('dashboard.inventory.index', [
            'inventories' => $rows,
            'warehouses' => Warehouse::orderBy('name_ar')->get(),
            'items' => Item::orderBy('name_ar')->get(),
            'byWarehouse' => $byWarehouse,
            'lowStockCount' => $this->lowStock->lowStockCount($filters),
            'expiringCount' => $this->lowStock->expiringRows(30, $filters)->count(),
            'filters' => $filters,
        ]);
    }

    public function movements(Request $request)
    {
        $filters = [
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
        ];

        return view('dashboard.inventory.movements', [
            'movements' => \App\Models\InventoryMovement::with(['item', 'pack', 'warehouse'])
                ->when($filters['warehouse_id'], fn ($q, $v) => $q->where('warehouse_id', $v))
                ->when($filters['item_id'], fn ($q, $v) => $q->where('item_id', $v))
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
            'items' => Item::orderBy('name_ar')->get(),
            'warehouses' => Warehouse::orderBy('name_ar')->get(),
        ]);
    }

    /**
     * Per-warehouse, per-batch, per-pack stock with thresholds.
     */
    public function alerts(Request $request, LowStockAlertService $lowStock)
    {
        $filters = [
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
        ];

        return view('dashboard.inventory.alerts', [
            'rows' => $lowStock->stockRows($filters),
            'warehouses' => Warehouse::orderBy('name_ar')->get(),
            'items' => Item::orderBy('name_ar')->get(),
            'filters' => $filters,
            'lowCount' => $lowStock->lowStockCount($filters),
            'expiringCount' => $lowStock->expiringRows(30, $filters)->count(),
        ]);
    }

    /**
     * Create the low-stock and expiry notifications now.
     */
    public function notifyAlerts(Request $request, LowStockAlertService $lowStock)
    {
        $filters = [
            'warehouse_id' => $request->integer('warehouse_id') ?: null,
            'item_id' => $request->integer('item_id') ?: null,
        ];

        $low = $lowStock->notifyAdmins($filters);
        $expiring = $lowStock->notifyExpiring(30);

        $created = ($low ? 1 : 0) + ($expiring ? 1 : 0);

        return back()->with(
            $created > 0 ? 'success' : 'error',
            $created > 0
                ? trans('messages.inventory_page.alerts_raised', ['count' => $created])
                : trans('messages.inventory_page.no_alerts')
        );
    }

    public function adjust(Request $request)
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
